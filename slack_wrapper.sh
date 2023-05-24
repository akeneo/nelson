#!/bin/bash
set -e

CMD=$@

source slack_wrapper.cfg
echo "SLACK_URL=$SLACK_URL"
echo "SLACK_CHANNEL=$SLACK_CHANNEL"

send_slack ()
{
  curl -X POST --data-urlencode 'payload={"channel": "'"$SLACK_CHANNEL"'", "username": "Nelson", "text": "'"$1"'\n`'"$CMD"'`", "icon_emoji": ":nelson:"}' $SLACK_URL --insecure
}

$CMD || send_slack "An error occured during synchronization..."
