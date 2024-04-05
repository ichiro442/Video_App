const { nowInSec, SkyWayAuthToken, SkyWayContext, SkyWayRoom, SkyWayStreamFactory, uuidV4 } = skyway_room;

// ビデオ表示領域の縦横比、ここは待機画像の縦横比に合わせる
let localVideoRatio = 0.75;
let remoteVideoRatio = 0.75;
// フッターの縦幅
const footerHeight = 100;

// 画面サイズ変更時にビデオ表示領域のレイアウトを修正する関数
function resize() {
  const localVideo = document.getElementById('local-video');
  const remoteVideo = document.getElementById('remote-video');

  // 各要素の隙間の広さを調整するパラメータ
  const px = 5;

  // 画面の横幅が1200px以下のデバイスで、かつ縦並びにしても自分の映像が小さくなりすぎ(100px以下)ないくらい縦幅に余裕があればスマホ・タブレット用レイアウトにする
  if (window.innerWidth <= 1200 && (window.innerHeight - footerHeight) / 2 - (window.innerWidth - 6 * px) / 3 * remoteVideoRatio - 6 * px > 100) {
    // スマホ・タブレット用レイアウト(縦並び)として表示位置・サイズを計算する
    // 相手映像のレイアウト
    remoteVideo.style.position = 'absolute';
    // 横幅はほぼ画面いっぱい
    const newRemoteWidth = window.innerWidth - 6 * px;
    // 縦幅は映像の縦横比を維持
    const newRemoteHeight = remoteVideoRatio * newRemoteWidth;
    remoteVideo.style.width = newRemoteWidth;
    remoteVideo.style.height = newRemoteHeight;
    // トップの位置は真ん中より少し下
    remoteVideo.style.top = (window.innerHeight - footerHeight) / 2 - newRemoteHeight / 3
    // 左端の表示位置はほぼ画面左端
    remoteVideo.style.left = 3 * px;

    // 自分映像のレイアウト
    localVideo.style.position = 'absolute';
    // 縦幅は相手映像上部で空いている領域より少し小さくする
    const newLocalHeight = (window.innerHeight - footerHeight) / 2 - newRemoteHeight / 3 - 6 * px;
    // 横幅は映像の縦横比を維持
    const newLocalWidth = newLocalHeight / localVideoRatio;
    localVideo.style.width = newLocalWidth;
    localVideo.style.height = newLocalHeight;
    // トップの位置はほぼ画面上端
    localVideo.style.top = 3 * px;
    // 左端の位置はほぼ画面左端
    localVideo.style.left = 3 * px;
  } else {
    // PC用レイアウト(横並び)として表示位置・サイズを計算する
    // 相手映像のレイアウト
    localVideo.style.position = 'absolute';
    // 横幅は画面の半分より少し小さく
    const newLocalWidth = window.innerWidth / 2 - 6 * px;
    // 縦幅は縦横比を維持
    const newLocalHeight = localVideoRatio * newLocalWidth;
    localVideo.style.width = newLocalWidth;
    localVideo.style.height = newLocalHeight;
    // トップの位置は映像が上下中央に位置するように設定
    localVideo.style.top = (window.innerHeight - footerHeight) / 2 - newLocalHeight / 2
    // 左端はほぼ画面左端
    localVideo.style.left = px;

    // 自分映像のレイアウト
    remoteVideo.style.position = 'absolute';
    // 横幅は画面の半分より少し小さく
    const newRemoteWidth = window.innerWidth / 2 - 6 * px;
    // 縦幅は縦横比を維持
    const newRemoteHeight = remoteVideoRatio * newRemoteWidth;
    remoteVideo.style.width = newRemoteWidth;
    remoteVideo.style.height = newRemoteHeight;
    // トップの位置は映像が上下中央に位置するように設定
    remoteVideo.style.top = (window.innerHeight - footerHeight) / 2 - newRemoteHeight / 2
    // 左端はほぼ画面の左右中央
    remoteVideo.style.left = window.innerWidth / 2 + px;
  }
}
resize();

// 映像表示を停止する関数
function offVideo(elementId, imgPath, videoMuteButtonImg, videoMuteButtonText) {
  // video要素を削除する
  const video = document.getElementById(elementId);
  video.remove();
  // 待機画像を表示するための要素を作成する
  const imgElement = document.createElement('img');
  imgElement.id = elementId;
  imgElement.src = imgPath;

  if(videoMuteButtonImg && videoMuteButtonText) {
    // 自分の映像を停止している
    // ビデオ開始/停止ボタンの表示内容を変更する
    localVideoRatio = 0.75;
    videoMuteButtonImg.src = 'video_off.png';
    videoMuteButtonText.innerText = 'ビデオの開始';
  }else{
    // 相手の映像が停止されている
    remoteVideoRatio = 0.75;
  }
  document.body.append(imgElement);
  resize();
}

// 映像表示を開始する関数
function onVideo(elementId, video, videoMuteButtonImg, videoMuteButtonText) {
  // 待機画像を表示している要素を削除する
  const element = document.getElementById(elementId);
  element.remove();
  // 映像を表示するための要素を作成する
  const videoElement = document.createElement('video');
  videoElement.id = elementId;
  video.attach(videoElement);
  videoElement.setAttribute('playsinline', '');
  videoElement.autoplay = true;
  const videoSettings = video.track.getSettings();

  if(videoMuteButtonImg && videoMuteButtonText) {
    // 自分の映像を開始している
    // ビデオ開始/停止ボタンの表示内容を変更する
    if(videoSettings.height && videoSettings.width) {
      localVideoRatio = videoSettings.height / videoSettings.width;
    }
    videoMuteButtonImg.src = 'video_on.png';
    videoMuteButtonText.innerText = 'ビデオの停止';
  }else{
    // 相手の映像が開始されている
    if(videoSettings.height && videoSettings.width) {
      remoteVideoRatio = videoSettings.height / videoSettings.width;
    }
  }
  document.body.append(videoElement);
  videoElement.play();
  resize();
}

// STEP1: SkyWayAuthTokenの生成
const appId = config.appId;
const secretKey = config.secretKey;
const token = new SkyWayAuthToken({
  jti: uuidV4(),
  iat: nowInSec(),
  exp: nowInSec() + 60 * 60 * 24,
  scope: {
    app: {
      id: appId,
      turn: true,
      actions: ['read'],
      channels: [
        {
          id: '*',
          name: '*',
          actions: ['write'],
          members: [
            {
              id: '*',
              name: '*',
              actions: ['write'],
              publication: {
                actions: ['write'],
              },
              subscription: {
                actions: ['write'],
              },
            },
          ],
          sfuBots: [
            {
              actions: ['write'],
              forwardings: [
                {
                  actions: ['write'],
                },
              ],
            },
          ],
        },
      ],
    },
  },
}).encode(secretKey);

(async () => {
  // 宣言・初期値の代入
  const localAudio = document.getElementById('local-audio');
  const audioMuteButton = document.getElementById('mute-audio-button');
  //const leaveButton = document.getElementById('leave-button');
  const remoteAudio = document.getElementById('remote-audio');
  let videoMuteButtonImg = document.getElementById('mute-video-img');
  let videoMuteButtonText = document.getElementById('mute-video-text');
  const maxNumberParticipants = 2;
  //leaveButton.disabled = true;

  // STEP3: SFURoomの作成（すでに作成中の場合はその情報を取得）
  const context = await SkyWayContext.Create(token);
  const roomId = new URLSearchParams(window.location.search).get("id");
  if (!roomId) {
    // Room名が分からない場合は警告を出して何もしない
    alert('Room名が不明です');
    return;
  }
  const room = await SkyWayRoom.FindOrCreate(context, {
    type: 'sfu',
    name: roomId,
  });

  // アプリ仕様上、Roomの最大参加人数を2名に制限する
  if (room.members.length >= maxNumberParticipants) {
    alert('最大参加人数(' + maxNumberParticipants + ')を超えています');
    room.dispose();
    return;
  }

  // STEP4: Roomに参加
  let me = await room.join();

  const footer = document.getElementById('footer');
  footer.style.height = footerHeight;

  // STEP2: 自分自身のカメラとマイクを取得して描画
  const { audio, video } = await SkyWayStreamFactory.createMicrophoneAudioAndCameraStream();
  audio.attach(localAudio);
  onVideo('local-video', video, videoMuteButtonImg, videoMuteButtonText);

  // STEP5: 自分の音声と映像をpublishする
  const localAudioPublication = await me.publish(audio);
  const localVideoPublication = await me.publish(video, {
    encodings: [
      { maxBitrate: 500_000, id: 'middle' },
    ],
  });

  // STEP6: 音声・映像の受信処理
  // STEP6-1: 音声・映像をsubscribeした時の処理
  const subscribeAndAttach = async (publication) => {
    // 自分の音声・映像だった場合は処理を終了
    if (publication.publisher.id === me.id) return;

    // 取得した音声・映像を受信
    const { stream, subscription } = await me.subscribe(publication.id);
    switch (stream.track.kind) {
      case 'video':
        onVideo('remote-video', stream);
        subscription.changePreferredEncoding('middle');

        // STEP7-2: 映像・音声OFFボタンをクリックした時の処理(受信側の処理)
        publication.onDisabled.add(() => {
          offVideo('remote-video', '1.png');
        });
        publication.onEnabled.add(() => {
          onVideo('remote-video', stream);
        })
        break;
      case 'audio':
        console.log('on audio');
        stream.attach(remoteAudio);
        break;
      default:
        return;
    }
  }

  // STEP6-2: Room入室時、すでにpublishされている音声・映像を受信するための処理
  room.publications.forEach(subscribeAndAttach);

  // STEP6-3: Room入室後に他のメンバーによってpublishされた音声・映像を受信するための処理
  room.onStreamPublished.add((e) => subscribeAndAttach(e.publication));

  // STEP7: 映像・音声OFFボタンをクリックした時の処理
  // STEP7-1: 映像・音声OFFボタンをクリックした時の処理(送信側の処理)
  let audioMuteButtonImg = document.getElementById('mute-audio-img');
  let audioMuteButtonText = document.getElementById('mute-audio-text');
  audioMuteButton.onclick = async () => {
    if (audioMuteButtonImg.src.endsWith('mic_on.png')) {
      await localAudioPublication.disable();
      audioMuteButtonImg.src = 'mic_off.png';
      audioMuteButtonText.innerText = 'ミュート解除';
    } else {
      await localAudioPublication.enable();
      audioMuteButtonImg.src = 'mic_on.png';
      audioMuteButtonText.innerText = 'ミュート';
    }
  }

  // 映像開始/停止ボタンの処理
  const videoMuteButton = document.getElementById('mute-video-button');
  videoMuteButton.onclick = async () => {
    if (videoMuteButtonImg.src.endsWith('video_on.png')) {
      await localVideoPublication.disable();
      offVideo('local-video', '0.png', videoMuteButtonImg, videoMuteButtonText);
    } else {
      await localVideoPublication.enable();
      onVideo('local-video', video, videoMuteButtonImg, videoMuteButtonText);
    }
  }

  resize();
  window.onresize = resize;
})();
