<?php

class CDocBuilder
{
    public $Output = "";
    public $OutputFile = "";
    public $Warnings = [];
    public $Errors = [];

    public $Language = "FR";
    public $Configuration = [];
    public $Dictionnary = [];
    public $Activity = [];
    public $Instance = [];
    public $Style = "";
    public $Type = "";
    public $Code = "html";

    public $GlobalMedals = [];
    public $MedalsDir = "";
    public $ChapterCount = 0;
    public $GivenMedals = [];

    public $ExercicePage = [];

    public $PageHeight = 0;
    public $LineHeight = 0;

    public $KeepTrace = false;
    public $Pretty = true;
};

function GetDocBuilder()
{
    return (new CDocBuilder);
}
