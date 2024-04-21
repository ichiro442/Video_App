// カウントダウンを表示する要素を取得
const countdownElement = document.getElementById('countdown');

// ビデオ表示用の要素を取得
const myVideo = document.getElementById('myVideo');
const partnerVideo = document.getElementById('partnerVideo');

// カウントダウンが終了したときの処理
function handleCountdownEnd() {
    // 自分のカメラを停止
    stopMediaStream(myVideo.srcObject);
    // 相手のカメラを停止
    stopMediaStream(partnerVideo.srcObject);
}

// ストリームを停止する関数
function stopMediaStream(stream) {
    if (!stream) return;
    const tracks = stream.getTracks();
    tracks.forEach(track => {
        track.stop();
    });
}

// ページ遷移時にカウントダウンの開始時間を削除する処理
window.addEventListener('unload', function() {
    localStorage.removeItem('startTime');
});

function updateCountdown() {
    // ローカルストレージから開始時間を取得し、存在しない場合は現在時刻を使用
    const startTimeString = localStorage.getItem('startTime');
    let startTime;
    if (startTimeString) {
        startTime = new Date(parseInt(startTimeString));
    } else {
        startTime = new Date();
        localStorage.setItem('startTime', startTime.getTime().toString());
    }

    // 現在の時間を取得
    const now = new Date();
    // 開始からの経過時間（ミリ秒）を計算
    const elapsedTime = now.getTime() - startTime.getTime();
    // 残り時間（ミリ秒）を計算
    const remainingTime = 25 * 60 * 1000 - elapsedTime;
    if (remainingTime <= 0) {
        countdownElement.textContent = 'ありがとうございました';

         // カウントダウンが終了したらrating.htmlに遷移する
         window.location.href = "rating.html";
        return;
    }

    // 分数と秒数に変換
    const minutes = Math.floor(remainingTime / (60 * 1000));
    const seconds = Math.floor((remainingTime % (60 * 1000)) / 1000);

	// カウントダウンを表示
    countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

    // 1秒ごとにカウントダウンを更新
    setTimeout(updateCountdown, 1000);
}

// 初回のカウントダウン更新
updateCountdown();
