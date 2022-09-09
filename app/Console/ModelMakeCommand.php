<?php

namespace App\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Commands\ModelMakeCommand as ParentModelMakeCommand;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends ParentModelMakeCommand
{
    public function handle(): int
    {
        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        $this->handleOptionalFactoryOption();

        return 0;
    }

    protected function getDestinationFilePath(): mixed
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $modelPath = GenerateConfigReader::read('model');

        return $path . $modelPath->getPath() . '/' . $this->getModelName() . 'Model.php';
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();

        return [
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model', null],
            ...$options,
        ];
    }

    /**
     * @return mixed|string
     */
    private function getModelName()
    {
        return Str::studly($this->argument('model'));
    }

    private function handleOptionalFactoryOption(): void
    {
        if ($this->option('factory') === true) {
            $factoryName = "{$this->getModelName()}";

            $this->call('module:make-factory', array_filter([
                'name' => $factoryName,
                'module' => $this->argument('module'),
            ]));
        }
    }
}
