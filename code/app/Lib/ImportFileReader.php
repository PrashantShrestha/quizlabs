<?php

namespace App\Lib;

use App\Models\Question;
use App\Models\QuestionOption;
use Exception;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ImportFileReader
{
    /*
    |--------------------------------------------------------------------------
    | Import File Reader
    |--------------------------------------------------------------------------
    |
    | This class basically generated for read data or insert data from user import file
    | several kind of files read here
    | like csv,xlsx,csv
    |
    */



    public $dataInsertMode = true;

    /**
     * colum name of upload file ,like name,email,mobile,etc
     * colum name must be same of target table colum name
     *
     * @var array
     */
    public $columns = [];

    /**
     * check the value exits on DB: table
     *
     * @var array
     */

    public $uniqueColumns = [];

    /**
     * on upload model class
     *
     * @var string
     */
    public $modelName;

    /**
     * upload file
     *
     * @var object
     */
    public $file;

    /**
     * supported input file extensions
     *
     * @var array
     */
    public $fileSupportedExtension = ['csv', 'xlsx', 'txt'];


    /**
     * Here store all data from read text,csv,excel file
     *
     * @var array
     */

    public $allData = [];

    /**
     * ALL Unique data store here
     */
    public $allUniqueData = [];

    public $notify = [];


    public function __construct($file, $modelName = null)
    {
        $this->file      = $file;
        $this->modelName = $modelName;
    }

    public function readFile()
    {
        $fileExtension = $this->file->getClientOriginalExtension();

        if (!in_array($fileExtension, $this->fileSupportedExtension)) {
            return $this->exceptionSet("File type not supported");
        }

        $spreadsheet = IOFactory::load($this->file);
        $data        = $spreadsheet->getActiveSheet()->toArray();

        if (count($data) <= 0) {
            return   $this->exceptionSet("File can not be empty");
        }

        $this->validateFileHeader(array_filter(@$data[0]));

        unset($data[0]);

        foreach ($data as  $item) {
            array_map('trim', $item);
            $this->dataReadFromFile($item);
        };

        return $this->saveData();
    }

    public function validateFileHeader($fileHeader)
    {
        if (!is_array($fileHeader) || count($fileHeader) != count($this->columns)) {
            $this->exceptionSet("Invalid file format");
        }

        foreach ($fileHeader as $k => $header) {
            if (trim(strtolower($header)) != strtolower(@$this->columns[$k])) {
                $this->exceptionSet("Invalid file format");
            }
        }
    }

    public function dataReadFromFile($data)
    {
        if (gettype($data) != 'array') {
            return $this->exceptionSet('Invalid data formate provided inside upload file.');
        }

        if (count($data) != count($this->columns)) {
            return  $this->exceptionSet('Invalid data formate provided inside upload file.');
        }

        if ($this->dataInsertMode && (!$this->uniqueColumCheck($data))) {
            $this->allUniqueData[] = array_combine($this->columns, $data);
        }

        $this->allData[] = $data;
    }

    function uniqueColumCheck($data)
    {

        $combinedData      = array_combine($this->columns, $data);
        $uniqueColumns     = array_intersect($this->uniqueColumns, $this->columns);
        $uniqueColumnCheck = false;

        foreach ($uniqueColumns as $uniqueColumn) {
            $uniqueColumnsValue = $combinedData[$uniqueColumn];
            if ($uniqueColumnsValue && $uniqueColumn) {
                $uniqueColumnCheck = $this->modelName::where($uniqueColumn, $uniqueColumnsValue)->exists();
            }
        }

        return $uniqueColumnCheck;
    }

    public function saveData()
    {
        if (count($this->allUniqueData) > 0 && $this->dataInsertMode) {
            try {
                if ($this->allUniqueData[0]['question']) {
                    $this->questionInsert($this->allUniqueData[0]);
                }else{
                    $this->modelName::insert($this->allUniqueData);
                }
            } catch (Exception $e) {
                $this->exceptionSet('This file can\'t be uploaded. It may contains duplicate data.');
            }
        }

        $this->notify = [
            'success' => true,
            'message' => count($this->allUniqueData) . " data uploaded successfully total " . count($this->allData) . ' data'
        ];
    }

    public function questionInsert($questionInfo)
    {
        $questionId = null;
        foreach ($questionInfo as $key => $value) {
            if ($key != 'question' && $key != 'answer') {
                $option = new QuestionOption();
                $option->question_id = $questionId;
                $option->option = $value;
                if (substr($key, -1) == $questionInfo['answer']) {
                    $option->is_answer = $questionInfo['answer'];
                }
                $option->save();
            } elseif ($key == 'question') {
                $question = new Question();
                $question->question     = $value;
                $question->save();

                $question->code = Str::random(2) . $question->id;
                $question->save();
                $questionId = $question->id;
            }
        }
    }

    public function exceptionSet($exception)
    {
        throw new Exception($exception);
    }

    public function getReadData()
    {
        return $this->allData;
    }

    public function notifyMessage()
    {
        $notify = (object) $this->notify;
        return $notify;
    }
}
