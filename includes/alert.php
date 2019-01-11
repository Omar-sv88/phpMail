<style>
    @keyframes fadeOut {
        0% { opacity: 1; }
        100% { opacity: 0; }
    }
    @-moz-keyframes fadeOut {
        0% { opacity: 1; }
        100% { opacity: 0; }
    }
    @-webkit-keyframes fadeOut {
        0% { opacity: 1; }
        100% { opacity: 0; }
    }
    @-o-keyframes fadeOut {
        0% { opacity: 1; }
        100% { opacity: 0; }
    }
    .alert {
        color: white !important;
        position: fixed;
        top: 45vh;
        left: 45vw;
        z-index: 9999;
        padding: 15px;
        -webkit-animation-name: fadeOut;
        animation-name: fadeOut;
        -webkit-animation-duration: 10s;
        animation-duration: 10s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
        cursor: default;
    }
    .alert.alert_error { background-color: red !important; }
</style>
<div class="clearfix"></div>
<?php switch ($status) : case 1: ?>
<div class="alert alert_error">
    <div class="alert_icon">
        <i class="icon-alert"></i>
    </div>
    <div class="alert_wrapper">
        <?php echo $msg; ?>
    </div>
</div>
<?php break; case 0: ?>
<div class="alert alert_success">
    <div class="alert_icon">
        <i class="icon-alert"></i>
    </div>
    <div class="alert_wrapper">
        <?php echo $msg; ?>
    </div>
</div>
<?php break; endswitch; ?>