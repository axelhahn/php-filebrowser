<?php

require_once __DIR__.'/../vendor/php-abstract-dbo/src/pdo-db-base.class.php';
require_once 'obj_fileindex.class.php';

class Filesearch
{

    /**
     * Subdir to scan ... or full path + given webroot
     * @var string
     */
    protected string $sDir;

    /**
     * relative dir to webroot
     * @var string
     */
    protected string $_sReldir = "";

    /**
     * indexFile with list of found files
     * @var string
     */
    protected string $indexFile;

    /**
     * timestamp of start for a measurement
     * @var float
     */
    protected float $iStart;

    /**
     * Last error messsage - it can be accessed by error()
     * @var string
     */
    protected string $_sError = "";

    /**
     * Configuration array with keys
     * - dir     {string}  start dir for file indexing
     * - webroot {string}  dir of webroot to be cut
     * @var array
     */
    protected array $_aConfig = [];

    protected object $_oDB;
    protected object $_oFileindex;
    protected string $_idx;

    // ----------------------------------------------------------------------
    // setter
    // ----------------------------------------------------------------------

    public function __construct(array $aConfig = [], string $sDirIdx = "")
    {
        $this->setConfig($aConfig, $sDirIdx);
    }

    // ----------------------------------------------------------------------
    // setter
    // ----------------------------------------------------------------------

    /**
     * Set the configuration for the file search and indexer
     * 
     * @param array $aConfig  array with config data
     * @param string $sDirIdx WIP: id of the directory to index
     * @return bool
     */
    public function setConfig(array $aConfig = [], string $sDirIdx = ""): bool
    {
        global $oDB;
        // reset data
        $this->sDir = "";
        $this->_sReldir = "";
        $this->_aConfig = [];

        $aDirConfig = $aConfig['dirs'][$sDirIdx] ?? $aConfig;
            // echo "Setting config for dir index '$sDirIdx' ...".PHP_EOL;
            // print_r($aDirConfig);
        if (!is_dir($aDirConfig['dir'] ?? "")) {
            $this->_sError = __FUNCTION__ . " directory does not exist: '" . ($aDirConfig['dir'] ?? "") . "'";
            return false;
        }
        $this->sDir = $aDirConfig['dir'] ?? "";
        $this->_idx = md5($this->sDir);
        $this->_aConfig = $aConfig;
        $this->_sReldir = ($aDirConfig['reldir'] ?? "");
        $this->_oDB=&$oDB;
        if(!$this->_oFileindex=new obj_fileindex($this->_oDB)){
            echo $this->_oDB->error()."<br>";
            return false;
        }
        return true;
    }

    /**
     * Start the timer before an action to measure.
     * Use _timer() to get the time in seconds since the last call of _startTimer()
     * 
     * @see _timer()
     * @return float
     */
    protected function _startTimer(): void
    {
        $this->iStart = microtime(true);
    }

    /**
     * Get the time in seconds since as float since the last call of _startTimer()
     * 
     * @see _startTimer()
     * @return float
     */
    protected function _timer(): float
    {
        return microtime(true) - $this->iStart;
    }

    /**
     * TODO: get the last error message
     * 
     * @return string
     */
    public function error(): string
    {
        return $this->_sError;
    }
    // ----------------------------------------------------------------------
    // actions
    // ----------------------------------------------------------------------

    /**
     * Get Entries of files and subdirs
     * 
     * @param string $sCurrentDir current subdir to scan
     * @return array|array{dir: string, hits: int, result: array, time: float, timestamp: int}
     */
    public function getDir(string $sCurrentDir): array
    {
        $this->_startTimer();
        $aReturn = [
            'dir' => $sCurrentDir,
            'hits' => 0,
            'result' => []
        ];

        $aData=[
            'idx' => $this->_idx,
            'subdir' => $sCurrentDir,
        ];
        $sWhere="idx = :idx AND path LIKE :subdir";
        foreach($this->_oFileindex->search([
                'columns'=>['*'],
                'where'=>$sWhere,
                'order'=>'ORDER BY file ASC',
            ], 
            $aData
        ) as $aItem){
            $aReturn['result'][] = $aItem;            
        }
        $aReturn['hits'] = count($aReturn['result']);
        $aReturn['time'] = $this->_timer();
        $aReturn['timestamp'] = time();
        return $aReturn;
    }

    /**
     * Action: refresh index of set directory
     * 
     * @return bool
     */
    public function refresh(): bool
    {
        $this->_startTimer();
        // $sRelDir = str_replace($this->_sWebroot, "", $this->sDir);

        ignore_user_abort(true);
        set_time_limit(0);
        
        echo '>>> Step 1 of 2 - Refreshing dir = '.$this->sDir.' ('. $this->_sReldir .')'.PHP_EOL;
        $iterator = new RecursiveDirectoryIterator($this->sDir);
        foreach (new RecursiveIteratorIterator($iterator) as $file) {
            if (
                basename((string) $file) == ".."
            ) {
                continue;
            }
            $sFiletype = 'other';
            if (is_dir((string) $file)) {
                $sFiletype = "dir";
                $file = dirname($file);
                if (
                    dirname((string) $file) == "."
                ) {
                    continue;
                }
            }
            if (is_file((string) $file)) {
                $sFiletype = "file";
            }

            $sFileBasename = basename($file);
            $bSkip=false;
            if (isset($this->_aConfig['exclude']['regex'])) {
                foreach ($this->_aConfig['exclude']['regex'] as $sExclude) {
                    if (preg_match("#$sExclude#", $sFileBasename)) {
                        echo "Skip $sFiletype '$file' - it matches $sExclude...".PHP_EOL;
                        $bSkip=true;
                        continue;
                    }
                }
            }
            if($bSkip){
                continue;
            }
            $sRelPath= "$this->_sReldir/".str_replace($this->sDir, "", dirname($file));
            $sRelPath=str_replace('//','/',$sRelPath);
            $sRelPath=preg_replace('#/$#','',$sRelPath);
            if($sFileBasename==$this->_sReldir){
                continue;
            }
            $aItem = [
                'idx' => $this->_idx,
                'type' => $sFiletype,
                'path' => $sRelPath,
                'file' => $sFileBasename,
                'modified' => filemtime($file),
                'size' => filesize($file)
            ];

            // $this->_oFileindex->new();
            if(!$this->_oFileindex->readByFields([
                'idx' => $this->_idx,
                'type' => $sFiletype,
                'path' => $sRelPath,
                'file' => $sFileBasename,
            ])) {
                // echo "Adding $sFiletype '$sRelPath/$sFileBasename'...".PHP_EOL;
                $this->_oFileindex->new();
            } else {
                // echo "Updating $sFiletype '$sRelPath/$sFileBasename'...".PHP_EOL;
            }
            $this->_oFileindex->setItem($aItem);
            if($this->_oFileindex->hasChange()){
                echo "Saving $sFiletype '$sRelPath/$sFileBasename'...".PHP_EOL;
                $this->_oFileindex->save();
            } else {
                echo "No changes for $sFiletype '$sRelPath/$sFileBasename'...".PHP_EOL;
            }

        }
        $iTotalTime = $this->_timer();
        $iDigits = $iTotalTime < 10 ? 3 : 0;
        $aStatus=$this->status();
        echo "Dirs: " . $aStatus['dirs'] .PHP_EOL;
        echo "Files: " . $aStatus['files'] . PHP_EOL;
        echo "Used time: " . round($iTotalTime, $iDigits) . "s for indexing".PHP_EOL;
        echo "Speed: " .number_format(round( ((int)$aStatus['dir']+(int)$aStatus['files']) / $iTotalTime ), 0). " file objects per second".PHP_EOL;
        echo PHP_EOL;

        $this->_startTimer();
        echo ">>> Step 2 of 2 - Cleanup deleted files...".PHP_EOL;
        $aData=[
            'idx' => $this->_idx,
        ];
        $sWhere="idx = :idx";
        foreach($this->_oFileindex->search([
                'columns'=>['*'],
                'where'=>$sWhere,
                'order'=>'ORDER BY path asc, file ASC',
            ], 
            $aData
        ) as $aItem){
            $sFilename="$this->sDir/".preg_replace('/'.$this->_sReldir.'/','',$aItem['path']) ."/$aItem[file]";
            $sFilename=str_replace('//','/',$sFilename);
            if(!file_exists($sFilename)){
                echo "Deleting '$sFilename' from index...".PHP_EOL;
                $this->_oFileindex->delete($aItem['id']);
            } else {
                // echo "Found '$sFilename'...".PHP_EOL;
            }
        }
        echo "Used time: " . round($iTotalTime, $iDigits) . "s for cleanup".PHP_EOL;

        return true;
    }


    /**
     * Search for a dir or filename in the given subfolder
     * It returns an array with the following keys:
     * - dir    {string}  the current dir
     * - hits   {int}     number of hits found
     * - query  {string}  the search query
     * - result {array}   array of found items with keys: type, path, file, modified, size
     * 
     * @param string $q
     * @param string $sSubdir
     * @return array{dir: string, hits: int, query: string, result: array}
     */
    public function search(string $q, string $sSubdir): array
    {
        $this->_startTimer();
        $aReturn = [
            'dir' => $this->sDir,
            'query' => $q,
            'hits' => 0,
            'result' => []
        ];
        $file = false;

        $sWhereFile="";
        $sWhereDir="";
        $aData=[];
        $iCount=0;


        foreach (explode(" ", $q) as $keyword) {
            $keyword = trim($keyword);
            $iCount++;
            $sVarKey="keyword$iCount";
            $aData[$sVarKey] = "%$keyword%";

            $sWhereFile.=($sWhereFile ? " AND " : "" ) . " file LIKE :$sVarKey";
            $sWhereDir.=($sWhereDir ? " AND " : "" ) . " path LIKE :$sVarKey";
        }
        $aData['idx'] = $this->_idx;
        $aData['subdir'] = "$sSubdir%";

        $sWhere="idx = :idx AND path LIKE :subdir AND ( ($sWhereFile) OR ($sWhereDir ))";

        foreach($this->_oFileindex->search([
                'columns'=>['*'],
                'where'=>$sWhere,
                'order'=>'ORDER BY path + file ASC',
            ], 
            $aData
        ) as $aItem){
            $aReturn['result'][] = $aItem;            
        }
        $aReturn['hits'] = count($aReturn['result']);
        $aReturn['time'] = $this->_timer();
        $aReturn['timestamp'] = time();
        return $aReturn;

    }

    /**
     * Get an arrry of the index status with counts of dirs and files and timestamp of last index
     * 
     * @return array{dir: string, dirs: int, files: int, time: bool|int|bool}
     */
    public function status(): array
    {
        $aReturn=[
            'dir'=>$this->sDir
        ];

        $aData['idx'] = $this->_idx;

        $aReturn['dirs']=$this->_oFileindex->makeQuery(
                "SELECT count(*) as items FROM obj_fileindex WHERE idx = :idx AND type = 'dir'", 
                $aData
            )[0]['items']??0;
        $aReturn['files']=$this->_oFileindex->makeQuery(
                "SELECT count(*) as items FROM obj_fileindex WHERE idx = :idx AND type = 'file'", 
                $aData
            )[0]['items']??0;
        $aReturn['timestamp']=date("U", strtotime($this->_oFileindex->makeQuery(
                "SELECT max(timecreated) as created FROM obj_fileindex WHERE idx = :idx", 
                $aData
            )[0]['created']??0));

        return $aReturn;
    
    }

}