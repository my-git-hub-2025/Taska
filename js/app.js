/* ============================================================
   Taska – Main JavaScript (jQuery)
   ============================================================ */

$(function () {

    /* ── Flash-message auto-dismiss ──────────────────────── */
    setTimeout(function () {
        $('.flash-alert').fadeOut(600, function () { $(this).remove(); });
    }, 4000);

    /* ── Media file preview ───────────────────────────────── */
    $('#mediaFiles').on('change', function () {
        var $preview = $('#mediaPreview').empty();
        $.each(this.files, function (i, file) {
            var url = URL.createObjectURL(file);
            if (file.type.startsWith('image/')) {
                $preview.append($('<img>').attr('src', url));
            } else if (file.type.startsWith('video/')) {
                $preview.append(
                    $('<video controls>').attr('src', url)
                );
            }
        });
    });

    /* ── Confirm delete ───────────────────────────────────── */
    $(document).on('click', '.btn-confirm-delete', function (e) {
        if (!confirm('Are you sure you want to delete this? This cannot be undone.')) {
            e.preventDefault();
        }
    });

    /* ── Category colour preview in post form ─────────────── */
    $('#category').on('change', function () {
        var cat = $(this).val();
        var classMap = {
            feed:    'cat-feed',
            rest:    'cat-rest',
            nappy:   'cat-nappy',
            hygiene: 'cat-hygiene',
            health:  'cat-health',
            other:   'cat-other'
        };
        $('#categoryBadge')
            .removeClass('cat-feed cat-rest cat-nappy cat-hygiene cat-health cat-other')
            .addClass(classMap[cat] || 'cat-other')
            .text(cat ? cat.charAt(0).toUpperCase() + cat.slice(1) : '');
    }).trigger('change');

    /* ── Notification polling (parent pages) ─────────────── */
    if ($('#notifCount').length) {
        // Poll every 30s via fetch
        setInterval(function () {
            fetch(window.TASKA_BASE + 'api/notification_count.php')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var $badge = $('#notifCount');
                    if (data.count > 0) {
                        $badge.text(data.count).show();
                    } else {
                        $badge.hide();
                    }
                })
                .catch(function () {});
        }, 30000);
    }

    /* ── Mark notification read on click ─────────────────── */
    $(document).on('click', '.notif-item[data-id]', function () {
        var id = $(this).data('id');
        $(this).removeClass('unread');
        fetch(window.TASKA_BASE + 'api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_read&id=' + encodeURIComponent(id)
        });
    });

    /* ── Role selector on register page ──────────────────── */
    $('#roleSelect').on('change', function () {
        var v = $(this).val();
        if (v === 'teacher') {
            $('#teacherNote').show();
        } else {
            $('#teacherNote').hide();
        }
    }).trigger('change');

    /* ── Data tables sort (simple click-to-sort) ──────────── */
    $(document).on('click', 'th[data-sort]', function () {
        var $th   = $(this);
        var $table = $th.closest('table');
        var col   = $th.index();
        var asc   = !$th.hasClass('sort-asc');
        $table.find('th').removeClass('sort-asc sort-desc');
        $th.addClass(asc ? 'sort-asc' : 'sort-desc');
        var $tbody = $table.find('tbody');
        var rows   = $tbody.find('tr').toArray();
        rows.sort(function (a, b) {
            var va = $(a).find('td').eq(col).text().trim().toLowerCase();
            var vb = $(b).find('td').eq(col).text().trim().toLowerCase();
            return asc ? va.localeCompare(vb) : vb.localeCompare(va);
        });
        $tbody.empty().append(rows);
    });

});
