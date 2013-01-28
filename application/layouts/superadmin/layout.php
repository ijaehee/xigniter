<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>SUPERADMIN</title>
    <?echo css_asset('bootstrap.css','bootstrap') ?>
    <?echo css_asset('/smoothness/jquery-ui-1.8.20.custom.css','jquery') ?>
    <?echo js_asset('jquery-1.7.2.min.js','jquery') ?>
    <?echo js_asset('jquery-ui-1.8.20.custom.min.js','jquery') ?>
    <?echo js_asset('bootstrap.min.js','bootstrap') ?>
    <?echo css_asset('admin.css','admin') ?>
    <?echo css_asset('docs.css','admin') ?>
    <script>
    var base_url = "<?=base_url()?>" ; 
    </script>
    <?= $_header_data?>
</head>
<body style="padding-top:20px;">
    <header class="navbar">
        <div class="navbar-inner navbar-fixed-top">
            <div>
                <ul class="nav"> 
                    <li <?php if($this->uri->segment(2)=='control_panel'):?> class="active" <?php endif;?>> 
                        <a href="<?=base_url()?>superadmin/control_panel/schemaList">CONTROL PANEL</a>
                    </li>
                </ul> 
            </div>
        </div>
    </header> 
    <div class="contents">
    <?= $_contents ?>
    </div> 

    <?= $_footer_data?>
</body>
</html>
