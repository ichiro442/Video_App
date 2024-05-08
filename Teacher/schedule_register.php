<script>
    const now = new Date();
    const events = JSON.parse('<?php echo json_encode($calendar) ?>');
    let addEvents = []; // 追加登録するデータの一覧　ここにデータを一時保管してhiddenへと移す
    let removeEvents = []; // 削除するデータの一覧　ここにデータを一時保管してhiddenへと移す

    // カレンダーの描画
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            firstDay: now.getDay(),
            displayEventTime: false,
            events: events,
            height: "auto",
            allDaySlot: false,
            selectable: true,
            selectMirror: true,
            // 選択したときの動作
            select: function(arg) {
                // 選択時の確認画面
                Swal.fire({
                    html: '<div class="mb-7">可否を入力ください</div>',
                    icon: "info",
                    showDenyButton: true,
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "○",
                    denyButtonText: "削除",
                    cancelButtonText: "Cancel",
                    customClass: {
                        confirmButton: "btn btn-primary",
                        denyButton: "btn btn-active-light",
                        cancelButton: "btn btn-active-light"
                    },
                }).then(function(result) {
                    let start = arg.start;
                    const end = arg.end;
                    if (result.value) {
                        let registStart = new Date(start.getTime());
                        calendar.getEvents().filter(s => {
                            return start <= s.start && s.end <= end
                        }).forEach(s => {
                            // フィリピンとの時差を考慮して60分進めて日本時間として登録
                            let registStart = new Date(s.start.getTime());
                            registStart.setMinutes(registStart.getMinutes() + 60);
                            // DBに登録したときに書式がわかるように書式を変更
                            const dateText = `${registStart.getFullYear()}-${registStart.getMonth() + 1}-${registStart.getDate()} ${registStart.getHours()}:${registStart.getMinutes()}:00`;
                            removeEvents.push({
                                "start": dateText
                            });
                            registStart = new Date(start.getTime());
                            s.remove();
                        });
                        while (start < end) {
                            let tmpEnd = new Date(start.getTime());
                            tmpEnd.setMinutes(tmpEnd.getMinutes() + 30);
                            calendar.addEvent({
                                title: "○",
                                start: start,
                                end: tmpEnd,
                                allDay: arg.allDay
                            })
                            // フィリピンとの時差を考慮して60分進めて日本時間として登録
                            let registStart = new Date(start.getTime());
                            registStart.setMinutes(registStart.getMinutes() + 60);
                            // DBに登録したときに書式がわかるように書式を変更
                            const dateText = `${registStart.getFullYear()}-${registStart.getMonth() + 1}-${registStart.getDate()} ${registStart.getHours()}:${registStart.getMinutes()}:00`;
                            addEvents.push({
                                "start": dateText,
                                "available": true
                            });
                            start = tmpEnd;
                        }
                        addEvents = Array.from(
                            new Map(addEvents.map((event) => [event.start, event])).values()
                        );
                        removeEvents = Array.from(
                            new Map(removeEvents.map((event) => [event.start, event])).values()
                        );
                        // hiddenとして追加したスケジュールを保持
                        document.querySelector('#addEvents').value = JSON.stringify(addEvents);
                        document.querySelector('#removeEvents').value = JSON.stringify(removeEvents);
                        // 選択状態を解除
                        calendar.unselect()
                        // 削除を押したときの動作　現在は動作しない
                    } else if (result.isDenied) {
                        const removeThisTimeEvents = [];
                        calendar.getEvents().filter(s => {
                            return start <= s.start && s.end <= end
                        }).forEach(s => {
                            // フィリピンとの時差を考慮して60分進めて日本時間として登録
                            let registStart = new Date(s.start.getTime());
                            registStart.setMinutes(registStart.getMinutes() + 60);
                            // DBに登録したときに書式がわかるように書式を変更
                            const dateText = `${registStart.getFullYear()}-${registStart.getMonth() + 1}-${registStart.getDate()} ${registStart.getHours()}:${registStart.getMinutes()}:00`;
                            removeEvents.push({
                                "start": dateText
                            });
                            removeThisTimeEvents.push(dateText);
                            s.remove();
                        });
                        removeEvents = Array.from(
                            new Map(removeEvents.map((event) => [event.start, event])).values()
                        );
                        // hiddenとして追加したスケジュールを保持
                        document.querySelector('#removeEvents').value = JSON.stringify(removeEvents);
                        // 削除した分を追加リストから除外
                        // SQLを実行を減らすため一括で削除⇒追加している。そのため追加分からも削除しないと整合が取れない。
                        addEvents = addEvents.filter(s => !removeThisTimeEvents.includes(s.start));
                        document.querySelector('#addEvents').value = JSON.stringify(addEvents);
                        calendar.unselect()
                    } else if (result.dismiss === 'cancel') {
                        calendar.unselect()
                    }
                });
            },
        });
        calendar.render();
    });
</script>