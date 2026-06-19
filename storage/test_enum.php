<?php

enum TaskStatus: string {
    case Todo = 'todo';
}

class Test {
    public string $status = TaskStatus::Todo->value;
}

echo "Success\n";
