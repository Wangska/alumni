<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM careers WHERE id=".$_GET['id'])->fetch_array();
    foreach($qry as $k => $v){
        $$k = $v;
    }
}
?>
<div class="w-full max-w-xl mx-auto bg-white p-8">
    <div class="flex flex-col gap-3 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center text-primary-600 shadow-inner">
                <i class="fas fa-building text-2xl"></i>
            </div>
            <div>
                <div class="text-xl font-bold text-primary-700"><?php echo ucwords($company) ?></div>
                <div class="text-sm text-primary-400 flex items-center gap-1">
                    <i class="fa fa-map-marker-alt"></i>
                    <span><?php echo ucwords($location ?? $company) ?></span>
                </div>
            </div>
        </div>
        <div>
            <div class="text-md text-primary-700 font-semibold flex items-center gap-2">
                <i class="fas fa-briefcase"></i>
                <?php echo ucwords($job_title) ?>
            </div>
        </div>
    </div>
    <hr class="my-4 border-primary-100">
    <div class="prose prose-primary max-w-none text-gray-700 text-base mb-6">
        <?php echo html_entity_decode($description) ?>
    </div>
</div>
<style>
    .prose-primary a { color: #dc2626; }
    .prose-primary h1, .prose-primary h2, .prose-primary h3 { color: #b91c1c; }
</style>
<script>
    $('.text-jqte').jqte();
    $('#manage-career').submit(function(e){
        e.preventDefault();
        start_load();
        $.ajax({
            url:'admin/ajax.php?action=save_career',
            method:'POST',
            data:$(this).serialize(),
            success:function(resp){
                if(resp == 1){
                    alert_toast("Data successfully saved.",'success')
                    setTimeout(function(){
                        location.reload()
                    },1000)
                }
            }
        })
    })
</script>