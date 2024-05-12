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

function updateCountdown() {
    // ローカルストレージから開始時間を取得し、存在しない場合は現在時刻を使用
    let startTimeString = localStorage.getItem('startTime');
    let startTime;

    // 開始時間が保存されている場合
    if (startTimeString) {
        // 文字列を数値に変換してDateオブジェクトを生成し、startTimeに代入
        startTime = new Date(parseInt(startTimeString));
    } else {  // 開始時間が保存されていない場合
        // 現在時刻を取得し、startTimeに代入
        startTime = new Date();
        // 開始時間をローカルストレージに保存する
        localStorage.setItem('startTime', startTime.getTime().toString());
    }

    // 現在の時間を取得
    const now = new Date();
    // 開始時間からの経過時間（ミリ秒）を計算
    const elapsedTime = now.getTime() - startTime.getTime();
    // 残り時間（ミリ秒）を計算
    const remainingTime = 25 * 60 * 1000 - elapsedTime;
    if (remainingTime <= 0) {
        submitForm();
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

// ページがロードされたときにカウントダウンを開始する
updateCountdown();
