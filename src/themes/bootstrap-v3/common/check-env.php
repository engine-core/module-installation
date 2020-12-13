<?php $this->title = '检查安装条件'; ?>

<h3>Conclusion</h3>
<?php if ($summary['errors'] > 0): ?>
    <div class="alert alert-danger">
        <strong>Unfortunately your server configuration does not satisfy the requirements by this application.<br>Please
            refer to the table below for detailed explanation.</strong>
    </div>
<?php elseif ($summary['warnings'] > 0): ?>
    <div class="alert alert-info">
        <strong>Your server configuration satisfies the minimum requirements by this application.<br>Please pay
            attention to the warnings listed below and check if your application will use the corresponding
            features.</strong>
    </div>
<?php else: ?>
    <div class="alert alert-success">
        <strong>Congratulations! Your server configuration satisfies all requirements.</strong>
    </div>
<?php endif; ?>

<h3>Details</h3>

<table class="table table-bordered">
    <tr>
        <th>Name</th>
        <th>Result</th>
        <th>Required By</th>
        <th>Memo</th>
    </tr>
    <?php foreach ($requirements as $requirement): ?>
        <tr class="<?php echo $requirement['condition'] ? 'success' : ($requirement['mandatory'] ? 'danger' : 'warning') ?>">
            <td>
                <?php echo $requirement['name'] ?>
            </td>
            <td>
                <span class="result"><?php echo $requirement['condition'] ? 'Passed' : ($requirement['mandatory'] ? 'Failed' : 'Warning') ?></span>
            </td>
            <td>
                <?php echo $requirement['by'] ?>
            </td>
            <td>
                <?php echo $requirement['memo'] ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<hr>
<footer>
    <p>Server: <?php echo $serverInfo . ' ' . $nowDate ?></p>
</footer>