<script>
    console.log("userType: " + <?php echo json_encode($_SESSION['userType']) ?>);
    console.log("flash_message: " + <?php echo json_encode($_SESSION['flash_message']) ?>);

    // モーダル
    $(function() {
        // フラッシュメッセージのデータを取得
        <?php if (isset($_SESSION['flash_message'])) : ?>
            var flash_message = <?php echo json_encode($_SESSION['flash_message']) ?>
        <?php else : ?>
            var flash_message = '';
        <?php endif; ?>

        // モーダル表示関数
        function showModal() {
            $('#modalArea').fadeIn();
            $('#flashMessageDisplay').text(flash_message); // モーダル内にフラッシュメッセージを表示
        }

        function deleteSession() {
            var currentURL = window.location.href;
            // currentURL = currentURL.split('?')[0];
            // console.log(url);
            var url = 'delete_flash.php';
            if (currentURL.indexOf("Teacher") !== -1 || currentURL.indexOf("Student") !== -1) {
                var url = '../delete_flash.php';
                console.log("URL: " + url);
            }
            $.ajax({
                url: url, // サーバーサイドのスクリプトのパスを指定
                type: 'GET', // リクエストのタイプを指定
                data: {
                    action: 'delete_flash'
                }, // サーバーに送信するデータを指定
                success: function(response) {
                    // セッションが削除された後の処理を記述
                    console.log('セッションが削除されました');
                },
                error: function(xhr, status, error) {
                    // エラーが発生した場合の処理を記述
                    console.error('エラーが発生しました:', error);
                }
            });
        }

        // 値が存在している場合にモーダルを表示
        if (flash_message !== "") {
            showModal();
        }
        // モーダルを閉じる処理
        $('#closeModal , #modalBg').click(function() {
            var flash_message = '';
            deleteSession();
            $('#modalArea').fadeOut();
        });
    });
</script>