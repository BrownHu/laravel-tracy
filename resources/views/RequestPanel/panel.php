<div id="Laravel-RequestPanel">
    <h1>Request</h1>
    <div class="tracy-inner">
        <?php if (empty($request) === true): ?>
            <p><i>empty</i></p>
        <?php else: ?>
            <table>
                <tbody>
                    <?php foreach ($request as $key => $value): ?>
                        <tr>
                            <th><?php echo strtoupper($key) ?></th>
                            <td>
                                <div id="Laravel-RequestPanel-<?php echo $key; ?>">
                                </div>
                                <script>
                                (function() {
                                    var el = document.getElementById("Laravel-RequestPanel-<?php echo $key; ?>");
                                    el.innerHTML = TracyDump(<?php echo json_encode($value) ?>);
                                })();
                                </script>
                                <?php
                                // echo Tracy\Dumper::toHtml($value, $dumpOption);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
</div>
