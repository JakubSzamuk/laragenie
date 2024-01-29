<?php

namespace JoshEmbling\Laragenie\Helpers;

use JoshEmbling\Laragenie\Helpers;

use function Laravel\Prompts\select;

trait Actions
{
    use Helpers\Formatting;

    public function welcome()
    {
        $this->newLine();
        $this->textWarning(config('laragenie.bot.welcome'));

        sleep(1);

        return select(
            'What do you want to do?',
            [
                'q' => 'Ask a question 🙋‍♂️',
                'i' => 'Index files 🗂',
                'r' => 'Remove indexed files 🚽',
                'o' => 'Something else 🤔',
            ],
        );
    }

    public function userAction()
    {
        sleep(1);

        $choice = select(
            'Do you want to do something else?',
            [
                'q' => 'Ask a question 🙋‍♂️',
                'i' => 'Index files 🗂',
                'r' => 'Remove indexed files 🚽',
                'x' => 'No thanks, goodbye! 👋',
            ],
        );

        match ($choice) {
            'q' => $this->askQuestion(),
            'i' => $this->askToIndex(),
            'r' => $this->askToRemoveIndexes(),
            'x' => $this->exitCommand(),
        };
    }

    public function continueAction()
    {
        return select(
            'Do you want to continue anyway?',
            [
                'y' => 'Yes',
                'n' => 'No',
            ],
        );
    }

    public function indexAction()
    {
        return select(
            'Do you want to index your directories and files saved in your config?',
            [
                'y' => 'Yes',
                'n' => 'No',
            ],
        );
    }

    public function removeAction()
    {
        return select(
            'What do you want to do?',
            [
                'one' => 'Remove data associated with a directory or specific file',
                'all' => 'Remove all chunked data',
            ],
        );
    }

    public function removeAllActionConfirm()
    {
        return select(
            'Are you sure? This cannot be undone!',
            [
                'y' => 'Yes',
                'n' => 'No',
            ],
        );
    }

    public function exitCommand()
    {
        exit;
    }
}
