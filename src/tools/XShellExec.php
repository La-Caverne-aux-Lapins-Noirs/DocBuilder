<?php

function XShellExec($cmd)
{
    // Ekko($cmd);
    return (shell_exec($cmd));
}
