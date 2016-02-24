<div id="Laravel-EventPanel">
    <h1>Events: <?php echo round($totalTime * 100, 2) ?> ms</h1>
    <div class="tracy-inner">
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Execute Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $key => $log): ?>
                    <tr>
                        <th>
                            <span class="tracy-dump-object"><?php echo array_get($log, 'firing') ?></span><br />
                            <?php echo array_get($log, 'editorLink') ?><br />
                        </th>
                        <td>
                            <div id="Laravel-EventPanel-<?php echo $key; ?>">
                            </div>
                            <script>
                            (function() {
                                var el = document.getElementById("Laravel-EventPanel-<?php echo $key; ?>");
                                el.innerHTML = TracyDump(<?php echo json_encode($log) ?>);
                            })();
                            </script>
                            <?php
                            // echo Tracy\Dumper::toHtml(array_get($log, 'params'), array_merge((array) $dumpOption, [
                            //     Tracy\Dumper::TRUNCATE => 50,
                            //     Tracy\Dumper::COLLAPSE => true,
                            // ]));
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
