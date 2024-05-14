<script>
    $(document).ready(function() {
        // カウントダウンの終了日時を設定
        let countdownDate = new Date("2024-12-12T00:00:00");
        console.log("レッスン開始時間" + start_time);

        let x = setInterval(function() {

            // 現在の日時を取得
            let now = new Date();

            // カウントダウンまでの残り時間を計算
            let distance = countdownDate - now;

            // 残り時間を日数、時間、分、秒に変換
            let days = Math.floor(distance / (1000 * 60 * 60 * 24));
            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // カウントダウンが終了した場合はメッセージを表示してタイマーを停止
            if (distance < 0) {
                clearInterval(x);
                $("#countdown").html("<span>カウントダウン終了</span>");
            } else {
                // 残り時間を表示
                $("#days").text(days + "日");
                $("#hours").text(hours + "時間");
                $("#minutes").text(minutes + "分");
                $("#seconds").text(seconds + "秒");
            }
        }, 1000); // 1000ミリ秒ごとに更新
    });
</script>