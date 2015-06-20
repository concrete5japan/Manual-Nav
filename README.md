# Manual Nav for concrete5.7

日本語の説明はスクロールしてください。(For Japanese, please scroll down)

Manual Nav is concrete5.7 version of Jordan Lev's famous Manual Nav but develop independently by acliss19xx from concrete5 Japan community.
http://www.concrete5.org/marketplace/addons/manual-nav/

- Ability to choose and add internal page manually
- Ability to add external URL manually
- You must enter a link text if you choose the external URL
- Ability to arrange the order of the menu
- Print out simple `<ul><li>`

You will need to modify the CSS in order to make it look like a navigation.

## Why Manual Nav?

Auto Nav block of concrete5 is great.

However, you don't want to create navigation automatically sometime but manually change the order due to the nature of the site.

Manual Nav enables you to pick and choose the page that you want to show on the navigation.

It will print out simple `<ul><li>` tags of link and text of the page.Of course, you can create your own custom template to add more page attribute.

We copied the Image Slider block and convert it to this Manual Nav.

## How to use

- Download Manual Nav from our [GitHub Repo](https://github.com/concrete5japan/Manual-Nav/archive/master.zip)
- Upload `manual_nav` folder to `packages` directory of your cocnrete5.7 site
- Install it from Extend concrete5 menu from dashboard
- Your Manual Nav will be added to Navigation block type set.
- Add the Manual Nav block to your desired area
- Pick and choose page or enter the external URL
- If you chose the external URL, you must enter the link text.
- If you didn't enter the link text, the page title will be used.

## Welcoming Pull Requests and Feature Requests

Please send your pull request and feature request to improve this add-on to our GitHub repo.
https://github.com/concrete5japan/Manual-Nav

## License

Manual Nav is licensed under MIT license. Please see the license.txt inside of manual_nav folder for the detail.

## Credit

- @katzueno (PM and document)
- @acliss19xx (Develop)

## Acknowledge

Thank you to Jordan Lev for letting us to use the name of Manual Nav. But we would like to see your awesome add-ons to support for 5.7 :)


# マニュアルナビ

オートナビやページリストとは違い、任意のページのリンクが作れるナビゲーションです。

concfete5.6 版の Jordan Lev さんが作成した「Manual Nav」と同等の機能を cocnrete5.7 で実現させたものです。
http://www.concrete5.org/marketplace/addons/manual-nav/

- concrete5 内部のページを手動で追加
- 外部リンクを手動で追加
- 外部リンクを入れた場合はリンクテキストが入力必須
- 自由にメニューの順番を入れ替えれます
- シンプルな `<ul><li>`タグを出力します

CSS で表示の調整をして頂く必要があります。

## 使い方

- Manual Nav を [GitHub レポジトリからダウンロード](https://github.com/concrete5japan/Manual-Nav/archive/master.zip)
- `manual_nav`フォルダを、concrete5.7 サイトの `packages`フォルダにアップロード
- 管理画面の concrete5 を拡張メニューから、Manual Nav をインストール
- ナビゲーションセクションに、Manual Nav が追加されます。
- Manual Nav を希望するエリアに配置
- 追加したいページを選んだり外部リンクを入力
- 外部URLを入力した場合はリンクテキストは入力必須です
- ページを選んで、リンクテキストに何も入力しない場合は、ページタイトルが表示されます

## ライセンス

Manual Nav は MIT ライセンスで配布しています。詳しくは manual_nav フォルダ内の license.txt をご覧ください。

## クレジット

- @katzueno (企画・ドキュメント)
- @acliss19xx (開発)


## プルリクエストや機能要望を募集します。

Pull Request や機能要望を GitHub で受け付けています！

https://github.com/concrete5japan/Manual-Nav

## 謝辞

Manual Nav という名前を使用してもよいと許諾していただいた Jordan Lev 氏に感謝します。

concrete5 Japan Users Group
