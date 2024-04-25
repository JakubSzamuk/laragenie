<?php

namespace JoshEmbling\Laragenie\Helpers;

use Laravel\Prompts\Output\ConsoleOutput;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use OpenAI\Responses\StreamResponse;

use function Laravel\Prompts\spin;

trait Chatbot
{
    use Actions;

    public function askBot(string $question): array
    {
        // Use OpenAI to generate context
        $openai_res = $this->openai->embeddings()->create([
            'model' => config('laragenie.openai.embedding.model'),
            'input' => $question,
            'max_tokens' => config('laragenie.openai.embedding.max_tokens'),
        ]);

        $pinecone_res = $this->pinecone->data()->vectors()->query(
            vector: $openai_res->embeddings[0]->toArray()['embedding'],
            topK: config('laragenie.pinecone.topK'),
        );

        if (empty($pinecone_res->json()['matches'])) {
            $this->textError('There are no indexed files.');
            $this->userAction();
        }

        return [
            'data' => $pinecone_res->json()['matches'],
            'vectors' => $openai_res->embeddings[0]->toArray()['embedding'],
        ];
    }

    public function botResponse(string $chunks, string $question): CreateStreamedResponse
    {
        $this->textNote('Generating answer...');

        try {
            $response_stream = $this->openai->chat()->createStreamed([
                'model' => config('laragenie.openai.chat.model'),
                'temperature' => config('laragenie.openai.chat.temperature'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => config('laragenie.bot.instructions').$chunks,
                    ],
                    [
                        'role' => 'user',
                        'content' => $question,
                    ],
                ],
            ]);
        } catch (\Throwable $th) {
            $this->textError($th->getMessage());
            $this->exitCommand();
        }

        $console_output = new ConsoleOutput();

        // Change the output text colour
        $console_output->write('<info>');

        $current_line = '';
        foreach ($response_stream as $response) {
            $response_new_data = $response->choices[0]->toArray();
            $new_token = $response_new_data['delta']['content'] ?? null;

            if (!$new_token) {
                continue;
            }

            // Wrap the text if the current line will be over 70 chars
            if (strlen($current_line . $new_token) > 70) {
                $current_line = ltrim($new_token);

                $console_output->write("\n" . $current_line);
            } else {
                $console_output->write($new_token);
                $current_line .= $new_token;
            }
        }
        $console_output->writeln("</info>\n\n");


    }
}
