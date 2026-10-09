<?php
// https://patorjk.com/software/taag/#p=display&f=Small+Braille&t=Axels+Filebrowser&x=none&v=4&h=4&w=80&we=false
/*

    ⣎⣱ ⡀⢀ ⢀⡀ ⡇ ⢀⣀   ⣏⡉ ⠄ ⡇ ⢀⡀ ⣇⡀ ⡀⣀ ⢀⡀ ⡀ ⢀ ⢀⣀ ⢀⡀ ⡀⣀
    ⠇⠸ ⠜⠣ ⠣⠭ ⠣ ⠭⠕   ⠇  ⠇ ⠣ ⠣⠭ ⠧⠜ ⠏  ⠣⠜ ⠱⠱⠃ ⠭⠕ ⠣⠭ ⠏     

    MAIN FILE
  
    @author Axel Hahn <https://github.com/axelhahn>

*/

$APPNAME = 'Axels file browser';
$VERSION = '0.7';

global $aConfig, $oDB;

require_once __DIR__ . '/vendor/php-abstract-dbo/src/pdo-db.class.php';
$aConfig = require __DIR__ . '/config.php';
$oDB = new axelhahn\pdo_db((array)$aConfig['pdo']??[]);

require_once __DIR__ . '/classes/filesearch.class.php';


// ----------------------------------------------------------------------
//
// M A I N  -  CLI
//
// ----------------------------------------------------------------------

if (php_sapi_name() == "cli") {
    $sDirIdx = '';
    $filesearch = new Filesearch($aConfig, $sDirIdx);

    echo "
    
    ⣎⣱ ⡀⢀ ⢀⡀ ⡇ ⢀⣀   ⣏⡉ ⠄ ⡇ ⢀⡀ ⣇⡀ ⡀⣀ ⢀⡀ ⡀ ⢀ ⢀⣀ ⢀⡀ ⡀⣀
    ⠇⠸ ⠜⠣ ⠣⠭ ⠣ ⠭⠕   ⠇  ⠇ ⠣ ⠣⠭ ⠧⠜ ⠏  ⠣⠜ ⠱⠱⠃ ⠭⠕ ⠣⠭ ⠏ 

                                       Version: $VERSION

👤 Author: Axel Hahn
🧾 Source: <https://github.com/axelhahn/php-filebrowser/>
📜 License: GNU GPL 3.0
📗 Docs: see <https://www.axel-hahn.de/docs/php-filebrowser/>

";

    if ($argc > 1) {
        $sAction = $argv[1];
        switch ($sAction) {
            case '-h':
            case '--help':
                echo "\n";
                echo "USAGE: $argv[0] [options] [parameters]\n";
                echo "\n";
                echo "OPTIONS:\n";
                echo "  -h, --help     Show this help message\n";
                echo "  -i, --index    Update Index with changed files\n";
                echo "  -r, --reindex  Reindex: delete Index and rebuild index from scratch\n";
                echo "\n";
                echo "PARAMETERS:\n";
                echo "  none\n";
                exit(0);
                break;

            // case '-d':
            // case '--dir':
            //     $sDirIdx = '';

            case '-i':
            case '--index':
                echo "Indexing files ...\n\n";
                $filesearch->refresh(false);
                echo "Done.\n";
                break;
            case '-r':
            case '--reindex':
                echo "Indexing files ...\n\n";
                $filesearch->refresh(true);
                echo "Done.\n";
                break;
            default:
                echo "Unknown action: $sAction\n";
                break;
        }
    } else {
        echo "Parameter required. Use -h or --help.\n";
    }
    exit(0);
}


// ----------------------------------------------------------------------
//
// M A I N  -  WEB
//
// ----------------------------------------------------------------------

/**
 * get icon ba filetype of given file.
 * 
 * @param string $sFile
 * @return string
 */
function getFileIcon(string $sFile): string
{
    global $aIco, $aTypes;
    $sExt=pathinfo($sFile, PATHINFO_EXTENSION);
    foreach ($aTypes as $sType => $aTypedata) {
        $aExtensionList=$aTypedata['ext'];
        if (in_array($sExt, $aExtensionList)) {
            return $aTypedata['icon'];
        }
    }
    return $aIco['file'];
}


/**
 * @var string searchterm
 */
$sSearchterm = isset($_GET['q']) ? $_GET['q'] : '';

$sAction = isset($_GET['action']) ? $_GET['action'] : '';

$sRelRoot = $aConfig['reldir']??'';

$sShowDir = isset($_GET['showdir']) ? $_GET['showdir'] : "";
$sShowDir = $sShowDir ?: $sRelRoot;

// $sDirIdx = explode('/', $sShowDir)[0] ?? '';
$sDirIdx = ''; // TODO: support multiple dirs to index and search

$filesearch = new Filesearch($aConfig, $sDirIdx);

// --- icons
$aIco = file_exists(__DIR__.'/config_icons.php') ? include 'config_icons.php' : null;
$aIco = $aIco ?: [
    'abort' => '❌',
    'back'  => '⬅️',
    'dir'   => '📁',
    'dirup' => '↖️',
    'file'  => '🔸',
    'home'  => '🏡',

    'reindex' => '🔁',
    'search'  => '🔎',
    'unknown' => '❓',
    'warning' => '⚠️',
];

// --- file types
$aTypes =  file_exists(__DIR__.'/config_types.php') ? include 'config_types.php' : null;
$aTypes=$aTypes ?: [
    
    'archive' => [
        'icon' => '🗜️',
        'ext' => [            
            'arj'  ,
            'bz'  ,
            'gz'  ,
            'rar'  ,
            'tar'  ,
            'tgz'  ,
            'zip'  ,
        ],
    ],
    'audio' => [
        'icon' => '🎵',
        'ext' => [            
            'm4a'  ,
            'mp3'  ,
            'ogg'  ,
            'wav'  ,
        ],
    ],
    'image' => [
        // 'icon' => '📷',
        'icon' => '🏙️',
        'ext' => [
            'bmp' ,
            'gif' ,
            'ico' ,
            'jpg' ,
            'jpeg',
            'png' ,
            'svg' ,
            'webp',
        ],
    ],
    'office' => [
        'icon' => '⌨️',
        'ext' => [            
            'doc'  ,
            'docx' ,
            'odt'  ,
            'ods'  ,
            'odp'  ,
            'ppt'  ,
            'pptx' ,
            'xls'  ,
            'xlsx' ,
        ],
    ],
    'packages' => [
        'icon' => '📦',
        'ext' => [            
            'apk'  ,
            'deb'  ,
            'msi'  ,
            'rpm'  ,
        ],
    ],

    'pdf' => [
        'icon' => '📰',
        'ext' => [
            'pdf'  ,
        ],
    ],

    'text' => [
        'icon' => '📝',
        'ext' => [
            'asp'  ,
            'css'  ,
            'js'   ,
            'json' ,
            'html' ,
            'log'  ,
            'md'   ,
            'php'  ,
            'py'  ,
            'rb'  ,
            'ts'   ,
            'txt'  ,
            'xml'  ,
        ],
    ],
    'video' => [
        'icon' => '🎬',
        'ext' => [
            'mp4'  ,
            'webm' ,
            'ogg'  ,
        ],
    ],
];
// --- language
$aLang=file_exists(__DIR__.'/config_lang.php') ? include 'config_lang.php' : null;
$aLang=$aLang ?: [
    'title' => 'File Browser',
    'search' => 'Search',
    'search_placeholder' => 'Type',

    'back' => 'Back',

    'indexing_start' => 'Indexing files ...',
    'index_status' => 'Index status',
        'dirs' => 'Dirs',
        'files' => 'Files',
        'timestamp' => 'Timestamp',
        'age' => 'Age',
        'reindex' => 'Reindex',

    // file table
    'type' => 'Type',
    'name' => 'Name',
    'size' => 'Size',
    'modified' => 'Modified',

    'go_up' => 'Go 1 directory level up',
    'open_directory' => 'Show directory',
    'open_file' => 'Show file',

    // search results
    'hits' => 'Hits',

    'current_dir' => 'Current directory',
];

$sSearchform = '
    <form action="?" method="get" class="frmsearch">
    <input type="hidden" name="showdir" value="' . $sShowDir . '">
    <input type="text" name="q" class="search" autofocus placeholder="'.$aLang['search_placeholder'].'" value="' . ($sSearchterm ? "$sSearchterm " : "") . '">
    <input type="submit" value="'.$aIco['search'].' '.$aLang['search'].'" class="button">
    ' . ($sSearchterm ? "<a href=\"?showdir=$sShowDir\" class=\"button\"> $aIco[abort] </a>" : "") . '
    </form>
';

$sStatusbox="";
$sBreadcrumb = "";


echo '
    <style>
    html{font-family: sans-serif; font-size: 1.0em; background: #fff;}
    body{margin: 0; padding: 2em 3%; height: 93vH; border-left: 10em solid #abc;}
    footer{border-top: 1px dotted #388; text-align: right;}
    footer span{color: #000a; font-size: 0.8em;}
    h1,h2{margin: 0;}
    h1{position: absolute; color: #abc1; top: 1.0em; font-size: 3em; z-index: -1;}
    h1 span{color: #cba2; font-size: 0.5em;}
    h2{margin: 0; color: #89c;}
    hr{border: none; border-top: 1px solid #eee;}
    a{color: #44c;text-decoration: none;}
    a.dir{color: #44c;text-decoration: none;}
    a.file{color: #555;text-decoration: none;}
    a:hover{text-decoration: underline;}

    code{background: #eee; font-family: "Courier new"; font-size: 1.0em; color: #f33; padding: 0.2em; border: 1px solid #daa; border-radius: 0.3em;}
    form.frmsearch{float: right;}
    pre{background: #f8f8f8; padding: 1em; border: 1px solid #ccc; border-radius: 1em; overflow: scroll; font-size: 1.2em; color: #336; }

    table{font-family: "Courier new"; font-size: 1.0em;width: 100%;}
    th{text-align: left; _background: #fff8e0; padding: 0.5em; border-bottom: 2px dotted #ddd;}
    tr.dir:hover{background:  #f0f4ff;}
    tr.file:hover{background: #f8f8f8;}
    td{padding: 0.3em 0.2em !important;}
    td a{display: block; padding: 0em 0.2em;}
    td a.file a.dir{display: inline}
    td.ico {width: 2%; text-align: right;}
    td.name {width: 70%;}
    td.size {width: auto; color: #848; text-align: right;}
    td.modified {width: 20%; color: #488;}

    .button { background: #f0f8f8; border-radius: 0.3em; border: 0px solid #0001; padding: 0.2em 0.5em; color: #333; text-decoration: none; font-size: 1.0em;}
    .button:hover{ background: #fec; text-decoration: none; }
    .main{background:#fff;}
    .context{ float: right; margin-left: 2em;}
    .contextbox{ background: #fff; padding: 1em; box-shadow: 0 0 3em #eee; margin-bottom: 2em;}
    .output{height: 80vH;  overflow: scroll;}
    .path {opacity: 0.5;}
    .search{font-size: 100%; color: #388; border: none; border-bottom: 2px solid #f8f8f8; width: 20vH;}
    .search:focus{outline: none;}
    </style>
';

// https://github.com/jstable/JSTable/tree/master/dist
echo <<<EOJSTABLE
    <style>
        .dt-container{position:relative;display:block;width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;-ms-overflow-style:-ms-autohiding-scrollbar;overflow-y:hidden}.dt-container .dt-message{text-align:center}.dt-container .dt-loading{position:absolute;top:50%;left:50%;width:100%;margin-left:-50%;margin-top:-20px;height:40px;text-align:center;background-color:#fff;display:flex;justify-content:center;align-items:center;background:linear-gradient(to right, rgba(255, 255, 255, 0) 0, rgba(255, 255, 255, 0.9) 25%, rgba(255, 255, 255, 0.9) 75%, rgba(255, 255, 255, 0) 100%)}.dt-top,.dt-bottom{padding:8px 10px;display:flex;justify-content:space-between}.dt-top .dt-selector,.dt-bottom .dt-selector{padding:6px}.dt-top .dt-info,.dt-bottom .dt-info{margin:7px 0}.dt-input{padding:6px 12px}.dt-pagination ul{margin:0;padding-left:0}.dt-pagination ul li{list-style:none;float:left}.dt-pagination a,.dt-pagination span{border:1px solid rgba(0,0,0,0);float:left;margin-left:2px;padding:6px 12px;position:relative;text-decoration:none;color:inherit}.dt-pagination a:hover{background-color:#d9d9d9}.dt-pagination .active a,.dt-pagination .active a:focus,.dt-pagination .active a:hover{background-color:#d9d9d9;cursor:default}.dt-pagination .dt-ellipsis span{cursor:not-allowed}.dt-pagination .disabled a,.dt-pagination .disabled a:focus,.dt-pagination .disabled a:hover{cursor:not-allowed;opacity:.4}.dt-pagination .pager a{font-weight:bold}.dt-table{max-width:100%;width:100%;border-spacing:0}.dt-table>tbody>tr>td,.dt-table>tbody>tr>th,.dt-table>tfoot>tr>td,.dt-table>tfoot>tr>th,.dt-table>thead>tr>td,.dt-table>thead>tr>th{vertical-align:top;padding:8px 10px;white-space:nowrap}.dt-table>thead>tr>th,.dt-table>thead>tr>td{vertical-align:bottom;text-align:left;border-bottom:1px solid #d9d9d9}.dt-table>tfoot>tr>th,.dt-table>tfoot>tr>td{vertical-align:bottom;text-align:left;border-top:1px solid #d9d9d9}.dt-table th{vertical-align:bottom;text-align:left}.dt-table th.dt-sorter{position:relative;cursor:pointer;padding-right:20px}.dt-table th.dt-sorter::before,.dt-table th.dt-sorter::after{content:"";height:0;width:0;position:absolute;right:7px;border-left:4px solid rgba(0,0,0,0);border-right:4px solid rgba(0,0,0,0);opacity:.2}.dt-table th.dt-sorter::before{border-top:4px solid #000;top:18px}.dt-table th.dt-sorter::after{border-bottom:4px solid #000;border-top:4px solid rgba(0,0,0,0);bottom:22px}.dt-table th.dt-sorter.asc::after,.dt-table th.dt-sorter.desc::before{opacity:.6}.hidden{display:none !important;opacity:0 !important}   
    </style>
    <script>
        const JSTableDefaultConfig={perPage:5,perPageSelect:[5,10,15,20,25],sortable:!0,searchable:!0,nextPrev:!0,firstLast:!1,prevText:"&lsaquo;",nextText:"&rsaquo;",firstText:"&laquo;",lastText:"&raquo;",ellipsisText:"&hellip;",truncatePager:!0,pagerDelta:2,classes:{top:"dt-top",info:"dt-info",input:"dt-input",table:"dt-table",bottom:"dt-bottom",search:"dt-search",sorter:"dt-sorter",wrapper:"dt-wrapper",dropdown:"dt-dropdown",ellipsis:"dt-ellipsis",selector:"dt-selector",container:"dt-container",pagination:"dt-pagination",loading:"dt-loading",message:"dt-message"},labels:{placeholder:"Search...",perPage:"{select} entries per page",noRows:"No entries found",info:"Showing {start} to {end} of {rows} entries",loading:"Loading...",infoFiltered:"Showing {start} to {end} of {rows} entries (filtered from {rowsTotal} entries)"},layout:{top:"{select}{search}",bottom:"{info}{pager}"},serverSide:!1,deferLoading:null,ajax:null,ajaxParams:{},queryParams:{page:"page",search:"search",sortColumn:"sortColumn",sortColumnName:"sortColumnName",sortDirection:"sortDirection",perPage:"perPage"},addQueryParams:!0,rowAttributesCreator:null,searchDelay:null,method:"GET"};class JSTable{constructor(e,t={}){let s=e;"string"==typeof e&&(s=document.querySelector(e)),null!==s&&(this.config=this._merge(JSTableDefaultConfig,t),this.table=new JSTableElement(s),this.currentPage=1,this.columnRenderers=[],this.columnsNotSearchable=[],this.columnNames=[],this.searchQuery=null,this.sortColumn=null,this.sortDirection="asc",this.isSearching=!1,this.dataCount=null,this.filteredDataCount=null,this.searchTimeout=null,this.pager=new JSTablePager(this),this._build(),this._buildColumns(),this.update(null===this.config.deferLoading),this._bindEvents(),this._emit("init"),this._parseQueryParams())}_build(){let e=this.config;this.wrapper=document.createElement("div"),this.wrapper.className=e.classes.wrapper;var t=["<div class='",e.classes.top,"'>",e.layout.top,"</div>","<div class='",e.classes.container,"'>","<div class='",e.classes.loading," hidden'>",e.labels.loading,"</div>","</div>","<div class='",e.classes.bottom,"'>",e.layout.bottom,"</div>"].join("");if(t=t.replace("{info}","<div class='"+e.classes.info+"'></div>"),e.perPageSelect){var s=["<div class='",e.classes.dropdown,"'>","<label>",e.labels.perPage,"</label>","</div>"].join(""),a=document.createElement("select");a.className=e.classes.selector,e.perPageSelect.forEach(function(t){var s=t===e.perPage,r=new Option(t,t,s,s);a.add(r)}),s=s.replace("{select}",a.outerHTML),t=t.replace(/\{select\}/g,s)}else t=t.replace(/\{select\}/g,"");if(e.searchable){var r=["<div class='",e.classes.search,"'>","<input class='",e.classes.input,"' placeholder='",e.labels.placeholder,"' type='text'>","</div>"].join("");t=t.replace(/\{search\}/g,r)}else t=t.replace(/\{search\}/g,"");this.table.element.classList.add(e.classes.table),t=t.replace("{pager}","<div class='"+e.classes.pagination+"'></div>"),this.wrapper.innerHTML=t,this.table.element.parentNode.replaceChild(this.wrapper,this.table.element),this.wrapper.querySelector("."+e.classes.container).appendChild(this.table.element),this._updatePagination(),this._updateInfo()}async update(e=!0){var t=this;this.currentPage>this.pager.getPages()&&(this.currentPage=this.pager.getPages());let s=t.wrapper.querySelector(" ."+t.config.classes.loading);if(s.classList.remove("hidden"),this.table.header.getCells().forEach(function(e,s){let a=t.table.head.rows[0].cells[s];a.innerHTML=e.getInnerHTML(),e.classes.length>0&&(a.className=e.classes.join(" "));for(let t in e.attributes)a.setAttribute(t,e.attributes[t]);a.setAttribute("data-sortable",e.isSortable)}),e)return this.getPageData(this.currentPage).then(function(e){t.table.element.classList.remove("hidden"),t.table.body.innerHTML="",e.forEach(function(e){t.table.body.appendChild(e.getFormatted(t.columnRenderers,t.config.rowAttributesCreator))}),s.classList.add("hidden")}).then(function(){t.getDataCount()<=0&&(t.wrapper.classList.remove("search-results"),t.setMessage(t.config.labels.noRows)),t._emit("update")}).then(function(){t._updatePagination(),t._updateInfo()});t.table.element.classList.remove("hidden"),t.table.body.innerHTML="",this.getDataCount()<=0&&(t.wrapper.classList.remove("search-results"),t.setMessage(t.config.labels.noRows)),this._getData().forEach(function(e){t.table.body.appendChild(e.getFormatted(t.columnRenderers,t.config.rowAttributesCreator))}),s.classList.add("hidden")}_updatePagination(){let e=this.wrapper.querySelector(" ."+this.config.classes.pagination);e.innerHTML="",e.appendChild(this.pager.render(this.currentPage))}_updateInfo(){let e=this.wrapper.querySelector(" ."+this.config.classes.info),t=this.isSearching?this.config.labels.infoFiltered:this.config.labels.info;if(e&&t.length){var s=t.replace("{start}",this.getDataCount()>0?this._getPageStartIndex()+1:0).replace("{end}",this._getPageEndIndex()+1).replace("{page}",this.currentPage).replace("{pages}",this.pager.getPages()).replace("{rows}",this.getDataCount()).replace("{rowsTotal}",this.getDataCountTotal());e.innerHTML=s}}_getPageStartIndex(){return(this.currentPage-1)*this.config.perPage}_getPageEndIndex(){let e=this.currentPage*this.config.perPage-1;return e>this.getDataCount()-1?this.getDataCount()-1:e}_getData(){return this._emit("getData",this.table.dataRows),this.table.dataRows.filter(function(e){return e.visible})}_fetchData(){var e=this;let t={searchQuery:this.searchQuery,sortColumn:this.sortColumn,sortColumnName:this.columnNames[this.sortColumn],sortDirection:this.sortDirection,start:this._getPageStartIndex(),length:this.config.perPage,datatable:1};t=Object.assign({},this.config.ajaxParams,t);let s=this.config.ajax+"?"+this._queryParams(t);return fetch(s,{method:this.config.method,credentials:"same-origin",headers:{Accept:"application/json","Content-Type":"application/json"}}).then(function(e){return e.json()}).then(function(t){return e._emit("fetchData",t),e.dataCount=t.recordsTotal,e.filteredDataCount=t.recordsFiltered,t.data}).then(function(e){let t=[];return e.forEach(function(e){t.push(JSTableRow.createFromData(e))}),t}).catch(function(e){console.error(e)})}_queryParams(e){return Object.keys(e).map(t=>encodeURIComponent(t)+"="+encodeURIComponent(e[t])).join("&")}getDataCount(){return this.isSearching?this.getDataCountFiltered():this.getDataCountTotal()}getDataCountFiltered(){return this.config.serverSide?this.filteredDataCount:this._getData().length}getDataCountTotal(){return this.config.serverSide?null!==this.config.deferLoading?this.config.deferLoading:this.dataCount:this.table.dataRows.length}getPageData(){if(this.config.serverSide)return this._fetchData();let e=this._getPageStartIndex();var t=this._getPageEndIndex();return Promise.resolve(this._getData()).then(function(s){return s.filter(function(s,a){return a>=e&&a<=t})})}async search(e){var t=this;if(this.searchQuery===e.toLowerCase())return!1;if(this.searchQuery=e.toLowerCase(),this.config.searchDelay){if(this.searchTimeout)return!1;this.searchTimeout=setTimeout(function(){t.searchTimeout=null},this.config.searchDelay)}return this.currentPage=1,this.isSearching=!0,this.searchQuery.length?(this.config.serverSide||this.table.dataRows.forEach(function(e){e.visible=!1,t.searchQuery.split(" ").reduce(function(s,a){var r;let i=e.getCells();return i=i.filter(function(e,s){if(t.columnsNotSearchable.indexOf(s)<0)return!0}),r=i.some(function(e,t){if(e.getTextContent().toLowerCase().indexOf(a)>=0)return!0}),s&&r},!0)&&(e.visible=!0)}),this.wrapper.classList.add("search-results"),this.update().then(function(){t._emit("search",e)})):(this.table.dataRows.forEach(function(e){e.visible=!0}),this.isSearching=!1,t.wrapper.classList.remove("search-results"),t.update(),!1)}sort(e,t,s=!1){var a=this;if(this.sortColumn=e||0,this.sortDirection=t,this.sortColumn<0||this.sortColumn>this.table.getColumnCount()-1)return!1;var r=this.table.header.getCell(this.sortColumn),i=this.table.dataRows;this.table.header.getCells().forEach(function(e){e.removeClass("asc"),e.removeClass("desc")}),r.addClass(this.sortDirection),this.config.serverSide||(i=i.sort(function(e,t){var s=e.getCellTextContent(a.sortColumn).toLowerCase(),r=t.getCellTextContent(a.sortColumn).toLowerCase();return s=s.replace(/(\$|\,|\s|%)/g,""),r=r.replace(/(\$|\,|\s|%)/g,""),s=isNaN(s)||""===s?s:parseFloat(s),r=isNaN(r)||""===r?r:parseFloat(r),""===s&&""!==r||!isNaN(s)&&isNaN(r)?"asc"===a.sortDirection?1:-1:""!==s&&""===r||isNaN(s)&&!isNaN(r)?"asc"===a.sortDirection?-1:1:"asc"===a.sortDirection?s===r?0:s>r?1:-1:s===r?0:s<r?1:-1}),this.table.dataRows=i),this.config.serverSide&&s||this.update(),this._emit("sort",this.sortColumn,this.sortDirection)}async paginate(e){var t=this;return this.currentPage=e,this.update().then(function(){t._emit("paginate",t.currentPage,e)})}_setQueryParam(e,t){if(!this.config.addQueryParams)return;const s=new URL(window.location.href);s.searchParams.set(this.config.queryParams[e],t),window.history.replaceState(null,null,s)}_bindEvents(){var e=this;this.wrapper.addEventListener("click",function(t){var s=t.target;if(s.hasAttribute("data-page")){t.preventDefault();let a=parseInt(s.getAttribute("data-page"),10);e.paginate(a),e._setQueryParam("page",a)}if("TH"===s.nodeName&&s.hasAttribute("data-sortable")){if("false"===s.getAttribute("data-sortable"))return!1;t.preventDefault();let a=s.classList.contains("asc")?"desc":"asc";e.sort(s.cellIndex,a),e._setQueryParam("sortColumn",s.cellIndex),e._setQueryParam("sortDirection",a)}}),this.config.perPageSelect&&this.wrapper.addEventListener("change",function(t){var s=t.target;if("SELECT"===s.nodeName&&s.classList.contains(e.config.classes.selector)){t.preventDefault();let a=parseInt(s.value,10);e._emit("perPageChange",e.config.perPage,a),e.config.perPage=a,e.update(),e._setQueryParam("perPage",a)}}),this.config.searchable&&this.wrapper.addEventListener("keyup",function(t){"INPUT"===t.target.nodeName&&t.target.classList.contains(e.config.classes.input)&&(t.preventDefault(),e.search(t.target.value),e._setQueryParam("search",t.target.value))})}on(e,t){this.events=this.events||{},this.events[e]=this.events[e]||[],this.events[e].push(t)}off(e,t){this.events=this.events||{},e in this.events!=!1&&this.events[e].splice(this.events[e].indexOf(t),1)}_emit(e){if(this.events=this.events||{},e in this.events!=!1)for(var t=0;t<this.events[e].length;t++)this.events[e][t].apply(this,Array.prototype.slice.call(arguments,1))}setMessage(e){var t=this.table.getColumnCount(),s=document.createElement("tr");s.innerHTML='<td class="'+this.config.classes.message+'" colspan="'+t+'">'+e+"</td>",this.table.body.innerHTML="",this.table.body.appendChild(s)}_buildColumns(){var e=this;let t=null,s=null;this.config.columns&&this.config.columns.forEach(function(a){isNaN(a.select)||(a.select=[a.select]),a.select.forEach(function(r){var i=e.table.header.getCell(r);if(void 0!==i){if(a.hasOwnProperty("render")&&"function"==typeof a.render&&(e.columnRenderers[r]=a.render),a.hasOwnProperty("sortable")){let r=!1;i.hasSortable?r=i.isSortable:(r=a.sortable,i.setSortable(r)),r&&(i.addClass(e.config.classes.sorter),a.hasOwnProperty("sort")&&1===a.select.length&&(t=a.select[0],s=a.sort))}a.hasOwnProperty("searchable")&&(i.addAttribute("data-searchable",a.searchable),!1===a.searchable&&e.columnsNotSearchable.push(r))}})}),this.table.header.getCells().forEach(function(a,r){null===a.isSortable&&a.setSortable(e.config.sortable),a.isSortable&&(a.addClass(e.config.classes.sorter),a.hasSort&&(t=r,s=a.sortDirection)),e.columnNames.push(a.columnName)}),null!==t&&e.sort(t,s,!0)}_merge(e,t){var s=this;return Object.keys(e).forEach(function(a){!t.hasOwnProperty(a)||"object"!=typeof t[a]||t[a]instanceof Array||null===t[a]?t.hasOwnProperty(a)||(t[a]=e[a]):s._merge(e[a],t[a])}),t}async _parseQueryParams(){const e=new URLSearchParams(window.location.search);let t=e.get(this.config.queryParams.perPage);if(t){t=parseInt(t),this.config.perPage=t,this.wrapper.querySelectorAll("."+this.config.classes.selector).forEach(function(e){e.querySelectorAll("option").forEach(e=>e.removeAttribute("selected")),e.value=t,e.querySelector(`option[value='${t}']`).setAttribute("selected","")}),this.update()}let s=e.get(this.config.queryParams.search);if(s){this.wrapper.querySelectorAll("."+this.config.classes.input).forEach(function(e){e.value=s}),await this.search(s)}let a=e.get(this.config.queryParams.page);a&&await this.paginate(parseInt(a));let r=e.get(this.config.queryParams.sortColumn);if(r){r=parseInt(r);let t=e.get(this.config.queryParams.sortDirection);t=null==t?"asc":t,this.sort(r,t)}}}class JSTableElement{constructor(e){this.element=e,this.body=this.element.tBodies[0],this.head=this.element.tHead,this.rows=Array.from(this.element.rows).map(function(e,t){return new JSTableRow(e,e.parentNode.nodeName,t)}),this.dataRows=this._getBodyRows(),this.header=this._getHeaderRow()}_getBodyRows(){return this.rows.filter(function(e){return!e.isHeader&&!e.isFooter})}_getHeaderRow(){return this.rows.find(function(e){return e.isHeader})}getColumnCount(){return this.header.getColumnCount()}getFooterRow(){return this.rows.find(function(e){return e.isFooter})}}class JSTableRow{constructor(e,t="",s=null){this.cells=Array.from(e.cells).map(function(e){return new JSTableCell(e)}),this.d=this.cells.length,this.isHeader="THEAD"===t,this.isFooter="TFOOT"===t,this.visible=!0,this.rowID=s;var a=this;this.attributes={},[...e.attributes].forEach(function(e){a.attributes[e.name]=e.value})}getCells(){return Array.from(this.cells)}getColumnCount(){return this.cells.length}getCell(e){return this.cells[e]}getCellTextContent(e){return this.getCell(e).getTextContent()}static createFromData(e){let t=document.createElement("tr");if(e.hasOwnProperty("data")){if(e.hasOwnProperty("attributes"))for(const s in e.attributes)t.setAttribute(s,e.attributes[s]);e=e.data}return e.forEach(function(e){let s=document.createElement("td");if(s.innerHTML=e&&e.hasOwnProperty("data")?e.data:e,e&&e.hasOwnProperty("attributes"))for(const t in e.attributes)s.setAttribute(t,e.attributes[t]);t.appendChild(s)}),new JSTableRow(t)}getFormatted(e,t=null){let s=document.createElement("tr");var a=this;for(let e in this.attributes)s.setAttribute(e,this.attributes[e]);let r=t?t.call(this,this.getCells()):{};for(const e in r)s.setAttribute(e,r[e]);return this.getCells().forEach(function(t,r){var i=document.createElement("td");i.innerHTML=t.getInnerHTML(),e.hasOwnProperty(r)&&(i.innerHTML=e[r].call(a,t.getElement(),r)),t.classes.length>0&&(i.className=t.classes.join(" "));for(let e in t.attributes)i.setAttribute(e,t.attributes[e]);s.appendChild(i)}),s}setCellClass(e,t){this.cells[e].addClass(t)}}class JSTableCell{constructor(e){this.textContent=e.textContent,this.innerHTML=e.innerHTML,this.className="",this.element=e,this.hasSortable=e.hasAttribute("data-sortable"),this.isSortable=this.hasSortable?"true"===e.getAttribute("data-sortable"):null,this.hasSort=e.hasAttribute("data-sort"),this.sortDirection=e.getAttribute("data-sort"),this.hasColumName=e.hasAttribute("data-col-name"),this.columnName=e.getAttribute("data-col-name"),this.classes=[];var t=this;this.attributes={},[...e.attributes].forEach(function(e){t.attributes[e.name]=e.value})}getElement(){return this.element}getTextContent(){return this.textContent}getInnerHTML(){return this.innerHTML}setClass(e){this.className=e}setSortable(e){this.isSortable=e}addClass(e){this.classes.push(e)}removeClass(e){this.classes.indexOf(e)>=0&&this.classes.splice(this.classes.indexOf(e),1)}addAttribute(e,t){this.attributes[e]=t}}class JSTablePager{constructor(e){this.instance=e}getPages(){let e=Math.ceil(this.instance.getDataCount()/this.instance.config.perPage);return 0===e?1:e}render(){var e=this.instance.config;let t=this.getPages(),s=document.createElement("ul");if(t>1){let a=1===this.instance.currentPage?1:this.instance.currentPage-1,r=this.instance.currentPage===t?t:this.instance.currentPage+1;e.firstLast&&s.appendChild(this.createItem("pager",1,e.firstText)),e.nextPrev&&s.appendChild(this.createItem("pager",a,e.prevText)),this.truncate().forEach(function(e){s.appendChild(e)}),e.nextPrev&&s.appendChild(this.createItem("pager",r,e.nextText)),e.firstLast&&s.appendChild(this.createItem("pager",t,e.lastText))}return s}createItem(e,t,s,a){let r=document.createElement("li");return r.className=e,r.innerHTML=a?"<span>"+s+"</span>":'<a href="#" data-page="'+t+'">'+s+"</a>",r}isValidPage(e){return e>0&&e<=this.getPages()}truncate(){var e,t=this,s=t.instance.config,a=2*s.pagerDelta,r=t.instance.currentPage,i=r-s.pagerDelta,n=r+s.pagerDelta,o=this.getPages(),l=[],c=[];if(this.instance.config.truncatePager){r<4-s.pagerDelta+a?n=3+a:r>this.getPages()-(3-s.pagerDelta+a)&&(i=this.getPages()-(2+a));for(var h=1;h<=o;h++)(1===h||h===o||h>=i&&h<=n)&&l.push(h);l.forEach(function(a){e&&(a-e==2?c.push(t.createItem("",e+1,e+1)):a-e!=1&&c.push(t.createItem(s.classes.ellipsis,0,s.ellipsisText,!0))),c.push(t.createItem(a==r?"active":"",a,a)),e=a})}else for(let e=1;e<=this.getPages();e++)c.push(this.createItem(e===r?"active":"",e,e));return c}}window.JSTable=JSTable;    
    </script>
EOJSTABLE;

if (file_exists(__DIR__.'/style.css')) {
    echo '<link rel="stylesheet" href="style.css">';
}
switch ($sAction) {
    case 'indexFile':
        echo '
            <h2>'.$aLang['indexing_start'].'</h2>
            <br><a href="?" class="button">'.$aIco['back'].' '.$aLang['back'].'</a><br><br>
            <div class="output"><pre>';
        $filesearch->refresh();

        echo '</pre></div><br><a href="?" class="button">'.$aIco['back'].' '.$aLang['back'].'</a><br><br>';
        die();
        break;
}

// generate breadcrumb
$sMypath = "";
foreach (explode("/", $sShowDir) as $sEntry) {
    if(!$sEntry) continue;
    $sMypath .= "/$sEntry";
    $sBreadcrumb .= ($sMypath < $sRelRoot)
        ? "$aIco[dir] $sEntry / "
        : "$aIco[dir] <a class=\"dir\" href=\"?showdir=$sMypath\" title=\"$aLang[open_directory]\n$sMypath\">$sEntry</a> / "
    ;
}
$sBreadcrumb = ($sBreadcrumb ? "<strong>$sBreadcrumb</strong>" : "");

if ($sSearchterm) {
    $aQueryResult = $filesearch->search($sSearchterm, $sShowDir);
    foreach(explode(" ", $sSearchterm) as $sWord){
        $sTitleSearch.=($sTitleSearch ? " &amp; " : "") . "<code>'".htmlentities($sWord)."'</code>";
    }
    $sTitleSearch=$aIco['search']." Search: $sTitleSearch <a href=\"?showdir=$sShowDir\" class=\"button\"> $aIco[abort] </a>";
    foreach ($aQueryResult['result'] as $aItem) {
        $sFile=$aItem['file'];

        $sSearchOut.="<tr class=\"$aItem[type]\">";
        switch ($aItem['type']) {
            case 'file':
                // $sSearchOut .= "$aIco[file] <a href=\"$aItem[path]/$aItem[file]\">$aItem[path]/$sFile</a><br>";
                $sSearchOut .= "
                    <td class=\"ico\">".getFileIcon($aItem['file'])."</td>
                    <td class=\"name\">
                        
                        <a class=\"file\" href=\"$aItem[path]/$aItem[file]\" title=\"$aLang[open_file]\n$aItem[path]/$aItem[file]\">
                                <span class=\"path\" onclick=\"window.location.href='?showdir=$aItem[path]'; return false;\" title=\"$aLang[open_directory]\n$aItem[path]\">$aItem[path]/</span>$sFile
                        </a>
                    </td>
                    <td class=\"size\">".number_format($aItem['size'],0)."</td>
                    <td class=\"modified\">".date('Y-m-d H:i:s', $aItem['modified'])."</td>
                ";
                break;
            case 'dir':
                // $sSearchOut .= "$aIco[dir] <a href=\"?showdir=$aItem[path]/$aItem[file]\">$aItem[path]/$sFile</a><br>";
                $sSearchOut.="
                    <td class=\"ico\">$aIco[dir]</td>
                    <td class=\"name\">
                        <a class=\"dir\" href=\"?showdir=$aItem[path]/$aItem[file]\" title=\"$aLang[open_directory]\n$aItem[path]/$sFile\">
                            <span class=\"path\">$aItem[path]/</span>$sFile
                        </a>
                    </td>
                    <td class=\"size\"></a>
                    <td class=\"modified\">".date('Y-m-d H:i:s', $aItem['modified'])."</td>
                ";
                break;
            default:
                // $sSearchOut .= "$aIco[unknown] $aItem[path]/$aItem[file]<br>";
        }
        $sSearchOut.="</tr>";
    }
    $sSearchOut="<h2>$sTitleSearch</h2>"
            . '<br>'
            . "$aLang[hits]: <strong>$aQueryResult[hits]</strong><br><br>"
        .($sSearchOut 
        ? ""
            ." <table id=\"table1\"><thead>
                <th data-sortable=\"false\">$aLang[type]</th>
                <th>$aLang[name]</th>
                <th>$aLang[size]</th>
                <th>$aLang[modified]</th>
            </thead>$sSearchOut</table>" 
        : ""
    );

} else {
    $aCurrentFiles = $filesearch->getDir($sShowDir);
    
    $sDirlisting = "";
    $sDirinfo = "";

    $iFiles=0;
    $iFileSize=0;
    $iDirs=0;

    // add list of file entries
    $sDirlisting .= "";

    if($sShowDir>$sRelRoot){
        $sDirlisting .= "
        <tr class=\"dir dirup\">
            <td class=\"ico\">$aIco[dirup]</td>
            <td class=\"name\"><a class=\"dir\" href=\"?showdir=".dirname($sShowDir)."\" title=\"$aLang[go_up]\n".dirname($sShowDir)."\">..</a></td>
            <td class=\"size\"></a>
            <td class=\"modified\">".date('Y-m-d H:i:s', $aItem['modified'])."</td>
        </tr>
        ";
    }

    foreach ($aCurrentFiles['result']??[] as $aItem) {
        $sDirlisting.="<tr class=\"$aItem[type]\">";
        switch ($aItem['type']) {
            case 'file':
                $iFiles++;
                $iFileSize+=$aItem['size'];
                // $sDirlisting .= "$aIco[file] <a href=\"$aItem[path]/$aItem[file]\">$aItem[file]</a><br>";
                $sDirlisting .= "
                    <td class=\"ico\">".getFileIcon($aItem['file'])."</td>
                    <td class=\"name\"><span class=\"hidden\">$aItem[type]</span><a class=\"file\" href=\"$aItem[path]/$aItem[file]\" title=\"$aLang[open_file]\n$aItem[path]/$aItem[file]\">$aItem[file]</a></td>
                    <td class=\"size\">".number_format($aItem['size'])."</td>
                    <td class=\"modified\">".date('Y-m-d H:i:s', $aItem['modified'])."</td>
                    ";

                if(preg_match('/readme\.(txt|md)$/i', $aItem['file'])){
                    $sBasedir=$aConfig['dir'] . str_replace($aConfig['reldir'], '', $aItem['path']);
                    $sInfofile="$sBasedir/$aItem[file]";
                    $sDirinfo .= "<br><strong>$aItem[file]</strong><br><pre>".file_get_contents($sInfofile)."</pre>";
                }
                break;
            case 'dir':
                $iDirs++;
                // $sDirlisting .= "$aIco[dir] <a href=\"?showdir=$aItem[path]/$aItem[file]\">$aItem[file]</a><br>";
                $sDirlisting .= "
                    <td class=\"ico\">$aIco[dir]</td>
                    <td class=\"name\"><span class=\"hidden\">$aItem[type]</span><a class=\"dir\" href=\"?showdir=$aItem[path]/$aItem[file]\" title=\"$aLang[open_directory]\n$aItem[path]/$aItem[file]\">$aItem[file]</a></td>
                    <td class=\"size\"></a>
                    <td class=\"modified\">".date('Y-m-d H:i:s', $aItem['modified'])."</td>
                    ";
                break;
            default:
                // $sDirlisting .= "$aIco[unknown] $aItem[path]/$aItem[file]<br>";
        }
        $sDirlisting.="</tr>";
    }
    $sStatusbox .= $aConfig['show_info']
        ? '<div class="contextbox">
            <h2>'.$aLang['current_dir'].'</h2>
            <strong>' . $aIco['dir'] . ' ' . basename($sShowDir) . '</strong><br>
            <hr>
            '.$aLang['dirs'].': ' . $iDirs . '<br>
            '.$aLang['files'].': ' . $iFiles . '<br>
            '.$aLang['size'].': ' . number_format($iFileSize) . '<br>
        </div>'
        :''
        ;

}
$sDirlisting=$sDirlisting ? "<table id=\"table1\"><thead>
        <th data-sortable=\"false\">$aLang[type]</th>
        <th>$aLang[name]</th>
        <th>$aLang[size]</th>
        <th>$aLang[modified]</th>
    </thead>$sDirlisting</table>" : "";


if($aConfig['show_index']){

    $aIndexstatus = $filesearch->status();
    if($aIndexstatus['timestamp']){
        $iIndexAge = time() - $aIndexstatus['timestamp'];
    }

    $sStatusbox .= '
        <div class="contextbox">
            <h2>'.$aLang['index_status'].'</h2>
            <strong>' . $aIco['dir'] . ' ' . $sRelRoot . '</strong><br>
            '.($aIndexstatus['timestamp']
                ? '
                    <hr>
                    '.$aLang['dirs'].': ' . $aIndexstatus['dirs'] . '<br>
                    '.$aLang['files'].': ' . $aIndexstatus['files'] . '<br>
                    <br>
                    '.$aLang['timestamp'].': ' . date("Y-m-d H:i", $aIndexstatus['timestamp']) . '<br>
                    '.$aLang['age'].': ' . $iIndexAge . ' sec<br>'
                // .'<pre>'.print_r($filesearch->status(), true).'</pre>'
                . '<br>'
            : '<br>'.$aIco['warning'] . ' No index yet.<br>An admin needs<br>to reindex first.<br><br>'
            )
            . ($aConfig['show_reindex']
                ? 
                ($aIndexstatus['files'] ==0 || (time()-$aIndexstatus['timestamp']) > ($aConfig['reindex_after']??60*60*24)
                    ? '<a href="?action=indexFile" class="button">' . $aIco['reindex'] . ' '.$aLang['reindex'].'</a><br>'
                    : ''
                    )
                : ''
                )
            // . '<pre>'.print_r($filesearch->status(), true).'</pre>'
        .'</div>';
}

$sStatusbox=$sStatusbox ? "<div class=\"context\">$sStatusbox</div>" : "";

// ----------------------------------------------------------------------
// OUTPUT
// ----------------------------------------------------------------------

echo $sStatusbox 
    . '<h1>'.$APPNAME.' <span>'.$VERSION.'</span></h1>'
    . '<span style="float:right;">'
        . $sSearchform
    .'</span>'
    // . '<br><br>'
    . $sBreadcrumb
    . '<br><br><br>'
    // . '<hr>'
    . '<div class="output">'
    . ($sSearchterm
        ? $sSearchOut
        : ''
        . $sDirlisting.$sDirinfo
    )
    . '</div>'
    ."<footer><a href=\"https://github.com/axelhahn/php-filebrowser/\">Github: $APPNAME <span>$VERSION</span></a> * GNU GPL 3</footer>"
    . "<script>let myTable = new JSTable('#table1',{perPage:5000, searchable: false, sortable: true, perPageSelect: false});</script>";

    // .'<pre>aCurrentFiles = '.print_r($aCurrentFiles, true).'</pre>'
;
