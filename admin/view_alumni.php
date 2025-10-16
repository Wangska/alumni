<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT a.*,c.course,Concat(a.lastname,', ',a.firstname,' ',a.middlename) as name from alumnus_bio a inner join courses c on c.id = a.course_id where a.id= ".$_GET['id']);
    foreach($qry->fetch_array() as $k => $val){
        $$k=$val;
    }
}
?>
<div class="w-full max-w-xl mx-auto">
    <div class="flex flex-col items-center pt-8 pb-4">
        <div class="w-28 h-28 rounded-full border-4 border-rose-200 bg-white shadow-lg flex items-center justify-center mb-2 overflow-hidden">
            <img src="assets/uploads/<?php echo $avatar ?>" alt="Avatar" class="w-full h-full object-cover rounded-full">
        </div>
        <h2 class="mt-2 text-2xl font-bold text-rose-800"><?php echo $name ?></h2>
        <div class="mt-1 flex items-center gap-2">
            <?php if($status == 1): ?>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-2xl text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                    <i class="fas fa-check-circle text-green-600"></i> Verified
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-2xl text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                    <i class="fas fa-exclamation-circle text-amber-600"></i> Unverified
                </span>
            <?php endif; ?>
        </div>
    </div>
    <hr class="my-4 border-rose-100">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 px-4">
        <div>
            <p class="mb-2 text-rose-700">Name: <span class="font-semibold text-rose-900"><?php echo $name ?></span></p>
            <p class="mb-2 text-rose-700">Email: <span class="font-semibold text-rose-900"><?php echo $email ?></span></p>
            <p class="mb-2 text-rose-700">Contact: <span class="font-semibold text-rose-900"><?php echo isset($contact) && !empty($contact) ? htmlspecialchars($contact) : 'N/A' ?></span></p>
            <p class="mb-2 text-rose-700">Connected To: <span class="font-semibold text-rose-900"><?php echo isset($connected_to) && !empty($connected_to) ? htmlspecialchars($connected_to) : 'N/A' ?></span></p>
            <p class="mb-2 text-rose-700">Batch: <span class="font-semibold text-rose-900"><?php echo $batch ?></span></p>
            <p class="mb-2 text-rose-700">Course: <span class="font-semibold text-rose-900"><?php echo $course ?></span></p>
        </div>
        <div>
            <p class="mb-2 text-rose-700">Gender: <span class="font-semibold text-rose-900"><?php echo $gender ?></span></p>
            <p class="mb-2 text-rose-700">Account Status: 
                <?php if($status == 1): ?>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-2xl text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                        <i class="fas fa-check-circle text-green-600"></i> Verified
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-2xl text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                        <i class="fas fa-exclamation-circle text-amber-600"></i> Unverified
                    </span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <hr class="my-4 border-rose-100">
    <div class="px-4">
        <p class="mb-2 text-rose-700">Registered On: <span class="font-semibold text-rose-900"><?php echo isset($date_created) && !empty($date_created) ? date('F j, Y', strtotime($date_created)) : 'N/A' ?></span></p>
        <?php if(isset($about_content) && !empty($about_content)): ?>
        <div class="mb-4">
            <h4 class="text-rose-800 font-semibold mb-2">About / Bio</h4>
            <div class="p-4 bg-white rounded-xl border border-rose-50 text-rose-900">
                <?php echo nl2br(htmlspecialchars($about_content)) ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-4">
            <?php if(isset($facebook) && !empty($facebook)): ?>
                <a href="<?php echo htmlspecialchars($facebook) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl shadow">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
            <?php endif; ?>
            <?php if(isset($twitter) && !empty($twitter)): ?>
                <a href="<?php echo htmlspecialchars($twitter) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-xl shadow">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
            <?php endif; ?>
            <?php if(isset($linkedin) && !empty($linkedin)): ?>
                <a href="<?php echo htmlspecialchars($linkedin) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 text-white rounded-xl shadow">
                    <i class="fab fa-linkedin-in"></i> LinkedIn
                </a>
            <?php endif; ?>
        </div>
    </div>
    <!-- <div class="flex justify-end gap-3 mt-8">
        <button class="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition-all" type="button" data-dismiss="modal">Close</button>
        <select id="statusDropdown" class="px-5 py-2 rounded-xl bg-rose-600 text-white font-semibold transition-all focus:outline-none">
            <option value="1" <?php if($status == 1) echo 'selected'; ?>>Verified</option>
            <option value="0" <?php if($status == 0) echo 'selected'; ?>>Unverified</option>
        </select>
    </div> -->
</div>
<script>
    $('#statusDropdown').change(function() {
        var newStatus = $(this).val();
        start_load();
        $.ajax({
            url:'ajax.php?action=update_alumni_acc',
            method:"POST",
            data:{id:<?php echo $id ?>,status:newStatus},
            success:function(resp){
                if(resp == 1){
                    alert_toast("Alumnus/Alumna account status successfully updated.")
                    setTimeout(function(){
                        location.reload()
                    },1000)
                }
            }
        });
    });
</script>