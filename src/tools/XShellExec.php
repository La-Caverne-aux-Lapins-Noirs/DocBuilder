<?php

function XShellExec($cmd)
{
    system("echo $cmd");
    return (shell_exec($cmd));
}
