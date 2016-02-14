<style>
#tracy-debug .laravel-TerminalPanel .tracy-inner {
    width: 700px;
    height: 500px;
    background: #000;
    overflow-x: hidden;
}

#tracy-debug .laravel-TerminalPanel #shell {
    margin: 10px 15px;
}

#tracy-debug .laravel-TerminalPanel .tracy-inner div,
#tracy-debug .laravel-TerminalPanel .tracy-inner span,
#tracy-debug .laravel-TerminalPanel .tracy-inner a {
    background: #000;
    color: rgb(170, 170, 170);
    font-family: monospace;
    font-size: 12px;
    font-style: normal;
    font-variant: normal;
    font-weight: normal;
}

#tracy-debug .laravel-TerminalPanel .tracy-inner a {
    color: #0f60ff !important;
}

#tracy-debug .laravel-TerminalPanel .tracy-inner a:hover {
    color: red !important;
    text-decoration: underline !important;
}
</style>
<div class="laravel-TerminalPanel">
    <h1>Terminal</h1>
    <div class="tracy-inner">
        <?php if (empty($html) === false): ?>
            <?php echo $html; ?>
        <?php else: ?>
            composer require recca0120/terminal
        <?php endif ?>
    </div>
</div>
