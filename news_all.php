<?php include("./header.php"); ?>

<script>
    $(document).ready(function() {
        getNewsArticles('<?php echo $UserLanguageCode?>');
    });
</script>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header"> <i class="fa fa-newspaper fa-lg"></i> <a href="javascript:location.reload(true)"><?php echo _("News archive"); ?></a>                    
                </div>
                <div class="card-body">
                    <table id="TableNewsArticles" class="table table-responsive table-borderless table-hover" cellspacing="0">
                    </table>
                </div>
            </div>
        </div>
    </div>    
</div>

<?php include("./footer.php"); ?>