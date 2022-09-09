<?php

// **************  ここにエンドポイントのURLを入力してください  *******************

$url = 'https://xxx.xxx.xxx.xxx:8001/orardf/api/v1/datasets/query/published/HERSYS';

//  *******************************************************************************

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
	// データ取得
	$contents = file_get_contents($url, false, $context);
	// 取得したデータを吐き出す
	echo $contents ;
	// POST or GET がある場合はここで終了
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
th {
    background:#EEE;
    border: solid 1px;
	padding:5px;
}
td {
    font-size:80%;
    border: solid 1px;
	padding:5px;
}
table {
	word-break: break-all;
    border-collapse:  collapse;
    width:  100%;
    table-layout: fixed;
}
.button {
	width:200px; 
	height:40px; 
	font-size:125%; 
	margin:0 0 15px 0;
}
</style>
<script>
function execute() {
	document.getElementById("results").innerHTML = "connecting...";
    var endpoint = location.href;
    var method = "POST";
	var query = document.getElementById("query").value;
    sparqlQuery(query,endpoint,method) ;
}
function sparqlQuery(queryStr,endpoint,method) { 
    var querypart = "query=" + encodeURIComponent(queryStr);
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open(method, endpoint, true);
    xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xmlhttp.setRequestHeader("Accept", "application/sparql-results+json");
    xmlhttp.onreadystatechange = function() {
        if(xmlhttp.readyState == 4) {
            if(xmlhttp.status == 200 || xmlhttp.status == 201 ) {
                onSuccessQuery(xmlhttp.responseText);
            } else {
                document.getElementById("results").innerHTML = "error" ;
            }
        }
    }
    xmlhttp.send(querypart);
}
function onSuccessQuery(text) {
	try {
			const jsonObj = JSON.parse(text);
			const head = jsonObj.head.vars;
			const rows = jsonObj.results.bindings;
			if (rows.length === 0) {
				document.getElementById("results").innerHTML = "<span style='font-size:125%;color:red;'>There is no data that matches the search condition.</span>" ;
				return;
			}
			makeTable(head, rows);

		} catch (error) {			
			document.getElementById("results").innerHTML = "<span style='font-size:125%;color:red;'>SPARQL syntax error</span>" ;
		}
}
function makeTable(head, rows) {
    let html = "<table><tr>";
    for (let i=0; i<head.length; i++) {
        html += "<th>" + head[i] + "</th>";
    }
    html += "</tr>";
    for (let i=0; i<rows.length; i++) {
        html += "<tr>";
        for (let j=0; j<head.length; j++) {
            let col = head[j];
            if(rows[i][col] != null){
				
				if(rows[i][col].value.slice(0,4) == "http"){
					
					html += "<td>" + "<a href ='" + rows[i][col].value + "' target='_blank'>" + rows[i][col].value + "</a>" + "</td>";
					
				}else{
					html += "<td>" + rows[i][col].value + "</td>";
				}
            }else{
                html += "<td></td>";
            }
        }
        html += "</tr>";
    }
    html += "</table>";	
    document.getElementById("results").innerHTML = '<input type="button" value="clear results" onclick="erase()" class="button">' ;
    document.getElementById("results").innerHTML += html;
}
function erase() {
    document.getElementById("results").innerHTML = '' ;
}
</script>
</head>
<body>
<div class="parent" id="parent">
<div class="inner">
<h2>SPARQL Endpoint URI： <a href="<?php echo (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
<?php echo (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
</a>
</h2>
<h2>SPARQL test form</h2>
<form id="form1" name="myForm"> 
<textarea id="query" style="width:100%; height:200px;">
SELECT *
WHERE{ ?s ?p ?o } 
LIMIT 10
</textarea>
<br>
<input type="button" value="execute" onclick="execute()" class="button">
</form>
<div id="results"></div>
</div>
</div>
</body>
</html>
