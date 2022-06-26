# laravel-line-members-card

このリポジトリでは、[PHP Laravel x React !！ LINEで動く会員証ライブコーディング vol.1 - connpass](https://linedevelopercommunity.connpass.com/event/250999/)のサンプルコードを公開しています。

[PHP Laravel x React ！！ LINEで動く会員証ライブコーディング vol.1](https://zenn.dev/tmitsuoka0423/books/handson-members-card-laravel-react-line)で解説しています。

## インストール

```bash
composer install
```

## 環境変数設定

```bash
cp .env.example .env
```

| 項目名 | 名前 | 参考URL |
| -- | -- | -- |
| `LINE_CHANNEL_ACCESS_TOKEN` | LINEチャネルアクセストークン | [LINE公式アカウントの作成 / LINE Botの初め方](https://zenn.dev/protoout/articles/16-line-bot-setup) |
| `LINE_CHANNEL_SECRET` | LINEチャネルシークレット | [LINE公式アカウントの作成 / LINE Botの初め方](https://zenn.dev/protoout/articles/16-line-bot-setup) |
| `AIRTABLE_KEY` | Airtable APIキー | [事前準備 > Airtable](https://zenn.dev/tmitsuoka0423/books/handson-members-card-laravel-react-line/viewer/preparing#airtable%E3%81%B8%E3%83%AD%E3%82%B0%E3%82%A4%E3%83%B3) |
| `AIRTABLE_BASE` | Airtable ベースID | [事前準備 > Airtable](https://zenn.dev/tmitsuoka0423/books/handson-members-card-laravel-react-line/viewer/preparing#airtable%E3%81%B8%E3%83%AD%E3%82%B0%E3%82%A4%E3%83%B3) |
| `AIRTABLE_TABLE` | Airtable テーブル名 | [事前準備 > Airtable](https://zenn.dev/tmitsuoka0423/books/handson-members-card-laravel-react-line/viewer/preparing#airtable%E3%81%B8%E3%83%AD%E3%82%B0%E3%82%A4%E3%83%B3) |

## 実行

```bash
php artisan serve
```
