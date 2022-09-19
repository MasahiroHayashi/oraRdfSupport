# Oracle RDF Graph Server の SPARQL ENDPOINT サポートアプリ
　LODチャレンジ2022のイベント <a href="https://lodc2022sparql.peatix.com/">SPARQLエンドポイントの使い方・作り方2022</a> にてオラクルさんから紹介があった、Oracle RDF Graph Server を構築し使ってみたところ、初めての方には少し使いにくいだろうな、と感じる点がいくつかありました。

・　エンドポイントのURLが長くて、グローバルIPとポート番号が丸見えで恥ずかしい<br>
　　例）https://129.213.57.216:8001/orardf/api/v1/datasets/query/published/HERSYS<br>
・　POSTアクセスができない（GETのみ可能）<br>
・　オレオレ証明書なのでブラウザの警告が出る<br>
・　ブラウザJSから直接エンドポイントに向けたAjax通信が通らない<br>
・　公開用のSPARQLクエリ入力フォーム画面がない（<a href="https://yasgui.triply.cc/">Yasgui</a>等を利用する必要あり）<br>
 
　これらの使いにくい点を補完するためのPHPアプリケーションを作成しましたのでどうぞご利用ください。もし変なところがあればイシューかプルリクなどで教えていただけると助かります。
 
## 設置方法

　事前にオラクルクラウドで RDF Graph Server を立てておいてください。詳しくは<a href="https://lodc2022sparql.peatix.com/">こちら</a>をご覧ください。<br>
　このリポジトリにある index.php をダウンロードし、テキストエディタで開いて、あなたが構築した Oracle RDF Graph Server のエンドポイントURLを上書きしてください。<br>
　次に、PHP7.0以上が使えるWEBサーバーに、その上書きした index.php を設置してください。無料レンタルサーバーなどでもよいです。<br>
　すでに index.html や index.php が存在するディレクトリに設置したい場合は、ファイル名を変更（sparql.php 等）してもOKです。<br>
 　見た目などを好みに改造して使っていただくのももちろんOKです。

## 使い方

　WEBサーバーに設置した index.php をエンドポイントとしてクエリを投げてみてください。結果がJSONで返ります。POST又はGETのどちらでもOKです。POSTやGETの key name は <b>query</b> としてください。<br>
　CORS（クロスオリジンリソースシェアリング）も許可しているため、ローカルや別サーバーからのAjaxでのリクエストも可能です。<br>
　また、POSTやGETパラメータなしで、ブラウザから index.php にアクセスすると、SPARQLクエリの簡易入力フォーム画面が表示されます。誰でも簡単にSPARQLを利用することができます。<br>
 
## 利用例
エンドポイントURLは下記のいずれでもOK<br>
　https://hersys.mirko.jp/sparql<br>
　https://hersys.mirko.jp/sparql/index.php<br>
 
GET例<br>
　https://hersys.mirko.jp/sparql?query=select+%3Fs+%3Fp+%3Fo+where+%7B%3Fs+%3Fp+%3Fo%7D+limit+3<br>
 
POST例<br>
　https://hersys.mirko.jp/sparql/post.html<br>
 
無料レンタルサーバー設置例<br>
　https://ss1.xrea.com/yookan.s1010.xrea.com/sp/

## お悩み中の点
　作成した Oracle RDF Graph Server のインスタンスに apache などでWEBサーバーを作ってこのプログラムを動作させることができれば一石二鳥だと思いましたが、"Permission denied" エラーによりうまく動作しません。よい方法があれば教えていただけると助かります。
