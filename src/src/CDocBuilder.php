<?php // @codeCoverageIgnoreStart

class CDocBuilder
{
    public $Output = "";
    public $OutputFile = "";
    public $Warnings = [];
    public $Errors = [];
    public $Debugs = [];

    public $Language = "FR";
    public $Dictionnary = [];

    public $Configuration = [];
    public $ConfigurationFile = "";
    public $ActivityFile = "";
    public $InstanceFile = "";
    
    public $Type = "";
    public $Style = "";
    public $Code = "";
    public $Pager = "";

    public $GlobalMedals = [];
    public $MedalsDir = "";
    public $ChapterCount = 0;
    public $GivenMedals = [];

    public $ExercicePage = [];

    public $PageHeight = 0;
    public $LineHeight = 0;
    public $TitleHeight = [];

    public $KeepTrace = false;
    public $Pretty = true;

    public $SubRecording = false;
};

function GetDocBuilder()
{
    return (new CDocBuilder);
}

// @codeCoverageIgnoreEnd
