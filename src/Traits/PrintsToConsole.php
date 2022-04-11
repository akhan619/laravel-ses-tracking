<?php

namespace Akhan619\LaravelSesTracking\Traits;

trait PrintsToConsole {    
    protected string $arrayStyle = 'fg=cyan';
    protected string $arrayKeyStyle = 'fg=magenta';
    protected string $arrayValueStyle = 'fg=blue';

    /**
    * Pretty print a array to the console
    *
    * @return void
    */
    protected function prettyPrintArray(array $data, string $name): void
    {        
        $this->console->io->title($name);
        $this->arrayWalkRecursiveWithKey($data, 1);
        $this->console->newLine();
    }

    /**
    * Recursive function to walk over the array.
    *
    * @return void
    */
    protected function arrayWalkRecursiveWithKey(array $data, int $level): void
    {
        if($level === 1) {
            $this->console->line('[', $this->arrayStyle);            
        }

        foreach($data as $key => $value){
            $leadingSpace = str_repeat(' ', $level*4);
            if(is_array($value)) {
                if(is_string($key)) {
                    $this->console->line($leadingSpace . '"' . "<$this->arrayKeyStyle>$key</>" . '"  =>  ' . "<$this->arrayStyle>[</>");
                } else {
                    $this->console->line($leadingSpace . "<$this->arrayKeyStyle>$key</>" . '  =>  ' . "<$this->arrayStyle>[</>");
                }
                $this->arrayWalkRecursiveWithKey($value, $level + 1);
                $this->console->line($leadingSpace . "<$this->arrayStyle>],</>");
            } else {
                if(is_string($key)) {
                    if(is_bool($value)) {
                        $this->console->line($leadingSpace . '"' . "<$this->arrayKeyStyle>$key</>" . '"  =>  ' . ($value ? "<$this->arrayValueStyle>True</>" : "<$this->arrayValueStyle>False</>") . ',');
                    } else {
                        $this->console->line($leadingSpace . '"' . "<$this->arrayKeyStyle>$key</>" . '"  =>  "' . "<$this->arrayValueStyle>$value</>" . '",');
                    }                    
                } else {
                    if(is_bool($value)) {
                        $this->console->line($leadingSpace . ($value ? "<$this->arrayValueStyle>True</>" : "<$this->arrayValueStyle>False</>") .',');
                    } else {
                        $this->console->line($leadingSpace . '"' . "<$this->arrayValueStyle>$value</>" .'",');
                    }
                }
            }
        }

        if($level === 1) {
            $this->console->line(']', $this->arrayStyle);
        }
    }
}