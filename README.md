# Oracle RDF Graph Server の SPARQL ENDPOINT お助けアプリ
　LODチャレンジ2022のイベント <a href="https://lodc2022sparql.peatix.com/">SPARQLエンドポイントの使い方・作り方2022</a> にてオラクルさんから紹介があった、Oracle RDF Graph Server を構築し使ってみたところ、初めての方には少し使いにくいだろうな、と感じる点がいくつかありました。

・　エンドポイントのURLが長くて、グローバルIPとポート番号が丸見えで恥ずかしい<br>
　　例）https://129.213.57.216:8001/orardf/api/v1/datasets/query/published/HERSYS<br>
・　POSTアクセスができない（GETのみ可能）<br>
・　オレオレ証明書なのでブラウザの警告が出る<br>
・　ブラウザJSから直接エンドポイントに向けたAjax通信が通らない<br>
・　公開用のSPARQLクエリ入力フォーム画面がない（<a href="https://yasgui.triply.cc/">Yasgui</a>等を利用する必要あり）<br>
 
　これらの使いにくい点を補完するための、ちょっとしたPHPアプリケーションを作成しましたので、どうぞご利用ください。
 
## 設置方法

　事前にオラクルクラウドで RDF Graph Server を立てておいてください。詳しくは<a href="https://lodc2022sparql.peatix.com/">こちら</a>をご覧ください。<br>
　このリポジトリにある index.php をダウンロードし、テキストエディタで開いて、あなたが構築した Oracle RDF Graph Server のエンドポイントURLを追記してください。<br>
　次に、PHP7.0以上が使えるWEBサーバーに、その index.php を設置してください。それだけです。ファイル名を変更してもOKです。

## 使い方

　WEBサーバーに設置した index.php をエンドポイントとしてクエリを投げてみてください。結果がJSONで返ります。POST又はGETのどちらでもOKです。POSTやGETの key name は <b>query</b> としてください。<br>
　CORS（クロスオリジンリソースシェアリング）も許可しているため、Ajaxでのリクエストも可能です。<br>
　また、POSTやGETパラメータなしで、ブラウザから index.php にアクセスすると、SPARQLクエリの簡易入力フォーム画面が表示されます。誰でも簡単にSPARQLを利用することができます。<br>
 
## 利用例
エンドポイントURLは下記のいずれでもOK<br>
　https://hersys.mirko.jp/sparql<br>
　https://hersys.mirko.jp/sparql/index.php<br>
 
GET例<br>
　https://hersys.mirko.jp/sparql?query=select+%3Fs+%3Fp+%3Fo+where+%7B%3Fs+%3Fp+%3Fo%7D+limit+3<br>
 
POST例<br>
　https://hersys.mirko.jp/sparql/post.html<br>
