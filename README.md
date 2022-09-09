# ！！！　作成中　！！！！<br>Oracle RDF Graph Server の<br> SPARQL ENDPOINT補助プログラム
　LODチャレンジ2022のイベント <a href="https://lodc2022sparql.peatix.com/">SPARQLエンドポイントの使い方・作り方2022</a> にてオラクルさんから紹介があった、Oracle RDF Graph Server を構築し使ってみたところ、初めての方には少し使いにくいだろうな、と感じる点がいくつかありました。

・　エンドポイントのURLが長くて、グローバルIPとポート番号が丸見えでイヤ<br>
　　例）https://129.213.57.216:8001/orardf/api/v1/datasets/query/published/HERSYS<br>
・　POSTアクセスができない（GETのみ可能）<br>
・　オレオレ証明書なのでブラウザの警告が出る<br>
・　ブラウザJSからのAJAX通信が通らない（CORSの不許可？）<br>
・　SPARQLクエリの入力フォーム画面がない（<a href="https://yasgui.triply.cc/">Yasgui</a>等を利用する必要あり）<br>
 
　これらの使いにくい点を補完するための、ちょっとしたPHPプログラムを作成しましたので、どうぞご利用ください。
 
## 設置方法

　まず index.php をテキストエディタで開いて、あなたが構築した Oracle RDF Graph Server のエンドポイントURLを追記してください。<br>
　次に、PHP7.0以上が使えるWEBサーバーに、その index.php を設置してください。それだけです。ファイル名を変更してもOKです。

## 使い方

　WEBサーバーに設置した index.php をエンドポイントとして、POST又はGETでクエリを投げてみてください。結果がJSONで返ります。<br>
　POSTやGETパラメータなしで、ブラウザから index.php にアクセスするとSPARQLクエリの簡易入力フォーム画面が表示されます。
 
