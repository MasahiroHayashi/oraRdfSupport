<?php
// **************  ここにエンドポイントのURLを入力してください  *******************

$url = 'https://xxx.xxx.xxx.xxx:8001/orardf/api/v1/datasets/query/published/HERSYS';

//  *******************************************************************************

// CORSを許可
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

// POSTがある場合
if(isset($_POST['query'])){
	$query = array('query' => $_POST['query']);
	getData($url,$query);
	
// GETがある場合
}else if(isset($_GET['query'])){
	$query = array('query' => $_GET['query']);
	getData($url,$query);
}

function getData($url,$query){
	// クエリ配列からURLエンコードされたクエリ文字列を生成
	$query = http_build_query($query, "", "&");
	// アクセスするURLに、クエリをGETパラメータとして付加
	$url = $url . '?' . $query ;
	// ヘッダーの作成
	$header  = array(
		"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
		"Accept: application/sparql-results+json"	
	);
	// コンテキスト作成
	// ※ SSLエラーを回避するため証明書チェックを外している
	$context = array(
		"http" => array(
			"method"  => "GET",
			"header"  => implode("\r\n", $header),
		),
		"ssl" => array(
			'verify_peer'      => false,
			'verify_peer_name' => false
		)
	);
	// ストリームコンテキストに変換
	$context = stream_context_create($context);
	
	// エラー時に例外をスローするように登録（Warningの警告エラーを例外としてキャッチするため）
	set_error_handler(function($errno, $errstr, $errfile, $errline) {
		if (!(error_reporting() & $errno)) {
			return;
		}
		throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
	});
	
	// データ取得
	try {
		$contents = file_get_contents($url, false, $context);
		// 取得したデータを吐き出す
		echo $contents ;
		
	// SPARQLに文法エラーがあり file_get_contents で例外が出た場合
	} catch ( Exception $ex ) {
		//JSON形式でエラーを返す
		echo '{ "status":"error" , "detail":"SPARQL syntax error" }' ;
	}
	// POST or GET がある場合はここで強制終了（HTMLを表示させない）
	die;
}

// POST / GET のどちらもない場合は下記の入力フォームを表示
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>SPARQL test form</title>
<style type="text/css">
.parent {width: 100%;}
.inner {
	width: 1000px;
	margin: auto;
}
table {
	border-collapse:  collapse;
	width:  100%;
	table-layout: fixed;
}
th {
	background:#EEE;
	border: solid 1px #AAA;
	padding:5px;
}
td {
	font-size:80%;
	border: solid 1px #AAA;
	padding:5px;
}
.button {
	width:200px; 
	height:40px; 
	font-size:125%; 
	margin:0 0 15px 0;
}
.errMsg {
	font-size:125%;
	color:red;
}
.downLoad {
	display:inline-block;
	text-align:center;
	width:160px;
	font-size:90%;
	margin:0 0 0 60px;
	background:#EEE;
}
.textArea {
	font-size:140%;
	width:100%;
	height:200px;	
}
.resOption {
	margin-left:20px;
	color: #CCC;
}
</style>
<script>
//CSVとJSONダウンロードのためのグローバル変数
let csvText = "" ;
let jsonText = "" ; 
let head ;
let rows ;
// ローディングアニメ用グローバル変数
let global_repeat;

function execute() {
"use strict";
	// ローディングアニメ
	let output = 'Loading･･';
	function repeatFunc() {
		if(output == 'Loading････････････････'){
			output = 'Loading';
		}
		output += '･' ;
		document.getElementById("connect").innerText = output;
		global_repeat = setTimeout(repeatFunc, 300);
	}
	repeatFunc() ;
	
    document.getElementById("results").innerHTML = '' ;
	const endpoint = location.href;
	const method = "POST";
	const query = document.getElementById("query").value;
	sparqlQuery(query,endpoint,method) ;
}
function sparqlQuery(queryStr,endpoint,method) {
"use strict";
	const querypart = "query=" + encodeURIComponent(queryStr);
	let xmlhttp = new XMLHttpRequest();
	xmlhttp.open(method, endpoint, true);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.setRequestHeader("Accept", "application/sparql-results+json");
	xmlhttp.onreadystatechange = function() {
		if(xmlhttp.readyState == 4) {
			if(xmlhttp.status == 200 || xmlhttp.status == 201 ) {
				onSuccessQuery(xmlhttp.responseText);
			} else {
				// エラー1：HTTPステータスエラー（インスタンスのWEBサーバー障害等）
				eraseResultArea();
				document.getElementById("results").innerHTML = "<span class='errMsg'>error1 : Server error</span>" ;
				return;
			}
		}
	}
	xmlhttp.send(querypart);
}
function onSuccessQuery(text) {
"use strict";
	let jsonObj ;
	// エラー2：PHP側で何らかのエラーが出てしまった場合（PHP側のtry-catchで捉えられないもののみ）
	try {
		jsonObj = JSON.parse(text); // JSONのパースができるかどうかで判定
	} catch (error) {
		eraseResultArea();
		document.getElementById("results").innerHTML = "<span class='errMsg'>error2 : Unexplained error</span>" ;
		return ;
	}
	// PHP側ではエラーは出ずJSONで結果が返った場合
	try {
		jsonText = JSON.stringify(jsonObj,undefined,1); // 整形してからグローバル変数に入れる
		head = jsonObj.head.vars;
		rows = jsonObj.results.bindings;
		if (rows.length === 0) {
			// エラー3：SPARQLは正しく書けているが結果が0件だった場合
			eraseResultArea();
			document.getElementById("results").innerHTML = "<span class='errMsg'>error3 : There is no data that matches the search condition.</span>" ;
			return;
		}
		makeTable(head, rows);
	} catch (error) {
		const errorJsonObj = JSON.parse(text);		
		let errorTable = "<table>" ;
		const keyArr = Object.keys(errorJsonObj);
		for (let i=0; i<keyArr.length; i++) {
			errorTable += "<tr><th style='width:170px;'>" + keyArr[i] + "</th><td>" + errorJsonObj[keyArr[i]] + "</td></tr>" ;
		}
		errorTable += "</table>" ;		
		let msg ;
		// エラー4：SPARQL文法は正しいが、Autonomous Database が「停止中」のときに出るエラー
		if(errorJsonObj.detail && errorJsonObj.detail.substr(0, 9) === "Exception"){
			msg = "<span class='errMsg'>error4 : SPARQL database may be down.</span>";
		// エラー5：SPARQL文法上のエラー（PHP側でキャッチしたものも含む）
		} else {
			msg = "<span class='errMsg'>error5 : SPARQL syntax error</span>";
		}
		eraseResultArea();
		document.getElementById("results").innerHTML = msg + errorTable ;
		return;
	}
}
function makeTable(head, rows) {
"use strict";
	let html =  '<input type="button" value="clear results" onclick="eraseResultArea()" class="button">' ;
	html += '<a href="javascript:void(0)" onclick="csvDownload()" id="downloadCs" class="downLoad">CSV Download</a>' ;
	html += '<a href="javascript:void(0)" onclick="jsonDownload()" id="downloadJs" class="downLoad">JSON Download</a>' ;
	html += "<table><tr>";
	for (let i=0; i<head.length; i++) {
		html += "<th>" + head[i] + "</th>";
	}
	html += "</tr>";
	let lang = "";
	let datatype = "";
	for (let i=0; i<rows.length; i++) {
		html += "<tr>";
		for (let j=0; j<head.length; j++) {
			let col = head[j];
			lang = "";
			datatype = "";
			if(rows[i][col] != null){
				if(rows[i][col]["xml:lang"]){
					lang = "<span class='resOption'>@" + rows[i][col]["xml:lang"] + "</span>" ;
				}
				if(rows[i][col]["datatype"]){
					datatype = rows[i][col]["datatype"].replace( 'http://www.w3.org/2001/XMLSchema#', 'xml:' );
					datatype = "<span class='resOption'>" + datatype + "</span>" ;
				}
				if(rows[i][col].value.slice(0,4) == "http"){
					html += "<td>" + "<a href ='" + rows[i][col].value + "' target='_blank'>" + rows[i][col].value + "</a></td>";
				}else{
					html += "<td>" + rows[i][col].value + lang + datatype + "</td>";
				}
			}else{
				html += "<td></td>";
			}
		}
		html += "</tr>";
	}
	html += "</table>";	
	clearTimeout(global_repeat); //アニメーション終了
	document.getElementById("connect").innerHTML = '' ;
	document.getElementById("results").innerHTML = html;
}
function eraseResultArea() {
"use strict";
	clearTimeout(global_repeat); //アニメーション終了
	document.getElementById("connect").innerHTML = '' ;
	document.getElementById("results").innerHTML = '' ;
	csvText = "";
	jsonText = ""; 
	head = "";
	rows = "";
}
function csvDownload() {
"use strict";
	for (let i=0; i<(head.length - 1); i++) {
		csvText += head[i] + ",";
	}
	csvText += head[head.length - 1] + "\r\n";
	for (let i=0; i<rows.length; i++) {
		for (let j=0; j<(head.length - 1); j++) {
			csvText += rows[i][head[j]].value + ",";
		}
		csvText += rows[i][head[head.length - 1]].value + "\r\n";
	}
	
	//以下出力（csvなので一応BOMをつける）
	const bom = new Uint8Array([0xEF, 0xBB, 0xBF]);
	const blob = new Blob([bom, csvText]);
	if (window.navigator.msSaveBlob) {
		window.navigator.msSaveOrOpenBlob(blob, 'result.csv');
	} else {
		document.getElementById("downloadCs").href = window.URL.createObjectURL(blob);
		document.getElementById("downloadCs").setAttribute('download', 'result.csv');
	}
}
function jsonDownload() {
"use strict";
	const blob = new Blob([jsonText]);
	if (window.navigator.msSaveBlob) {
		window.navigator.msSaveOrOpenBlob(blob, 'result.json');
	} else {
		document.getElementById("downloadJs").href = window.URL.createObjectURL(blob);
		document.getElementById("downloadJs").setAttribute('download', 'result.json');
	}
}
</script>
</head>
<body>
<div class="parent">
<div class="inner">
<h2>SPARQL Endpoint URI： <a href="<?php echo (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
<?php echo (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
</a>
</h2>
<h2>SPARQL test form</h2>
<form> 
<textarea id="query" class="textArea">
SELECT *
WHERE{ ?s ?p ?o } 
LIMIT 20
</textarea>
<br>
<input type="button" value="execute" onclick="execute()" class="button">
</form>
<div id="results"></div>
</div>
</div>
</body>
</html>