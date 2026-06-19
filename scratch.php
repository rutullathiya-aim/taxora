<?php

enum Role: string
{
    case Staff = 'staff';
}

class Test
{
    public string $role = Role::Staff->value;
}

echo "ok\n";
