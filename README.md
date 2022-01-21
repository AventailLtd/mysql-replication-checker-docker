# MySQL replication checker - Docker

MySQL replication status checker daemon in docker image with Slack notification.

## Envs

| Name | Description |
| ------------- | ------------- |
| MYSQL_HOST | MySQL host address (required) |
| MYSQL_PORT | MySQL host port |
| MYSQL_USERNAME | MySQL username (required) |
| MYSQL_PASSWORD | MySQL password (required) |
| MYSQL_DATABASE | MySQL database name (required) |
| SLACK_URL | Slack webhook message url (required) |
| SUCCESS_MIN_ELAPSED_TIME  | Min elapsed time before renotify success message (`0` means disabled) |
| ERROR_MIN_ELAPSED_TIME  | Min elapsed time before renotify error message (`0` means disabled) |

## Usage

1. Build 
```shell
docker build -t mysqlchecker .
```
2. Run docker image
```shell
docker run -e MYSQL_HOST=127.0.0.1 -e MYSQL_USER=root -e MYSQL_PASSWORD=secret -e MYSQL_DATABASE=yourdb -e SLACK_URL=https://hooks.slack.com/services/XXXXXXX/XXXXXXX/XXXXXXX -e ERROR_MIN_ELAPSED_TIME=1800 mysqlchecker
```

## Notification working

Sends a Slack message if:
- First check
- State changed (success -> error, error -> success)
- Renotify enabled with `SUCCESS_MIN_ELAPSED_TIME` or `ERROR_MIN_ELAPSED_TIME` envs.
