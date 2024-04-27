<script>
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

        // 値が存在している場合にモーダルを表示
        if (flash_message !== "") {
            showModal();
        }
        // モーダルを閉じる処理
        $('#closeModal , #modalBg').click(function() {
            var flash_message = '';
            $('#modalArea').fadeOut();
        });
    });
</script>