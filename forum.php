<?php 
session_start();
include 'admin/db_connect.php'; 
?>
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- FontAwesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<!-- Landing-style Navigation (copied from index.php) -->
<style>
    .navbar-scrolled { backdrop-filter: blur(10px); background: rgba(127, 29, 29, 0.95) !important; }
    .glass-effect { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(255, 255, 255, 0.95); border: 1px solid rgba(209, 213, 219, 0.3);}
    .glass-dark { backdrop-filter: blur(16px) saturate(180%); background-color: rgba(127, 29, 29, 0.95); border: 1px solid rgba(185, 28, 28, 0.3);}
    .nav-link { position: relative; overflow: hidden;}
    .nav-link::before { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 2px; background: linear-gradient(90deg,transparent,#fca5a5,transparent); transition: all 0.3s ease; transform: translateX(-50%);}
    .nav-link:hover::before { width: 100%; }
</style>

<header id="mainNav" class="fixed top-0 w-full z-40 py-4 transition-all duration-300 glass-dark shadow-lg backdrop-blur-lg">
    <div class="container mx-auto px-6">
        <div class="flex justify-between items-center">
            <a href="./" class="text-2xl font-bold text-white hover:text-red-300 transition-colors duration-300">
                Alumni Nexus
            </a>

            <button id="mobile-menu-btn" class="md:hidden text-white p-2 rounded-lg hover:bg-white/10 transition-colors">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <nav id="desktop-nav" class="hidden md:flex items-center space-x-8">
                <a href="index.php?page=home" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                    <i class="fas fa-home mr-2"></i>Home
                </a>
                <a href="alumni_list.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                    <i class="fas fa-users mr-2"></i>Alumni
                </a>
                <a href="gallery.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                    <i class="fas fa-images mr-2"></i>Gallery
                </a>
                <div id="auth-links" class="flex items-center space-x-6">
                <?php if(isset($_SESSION['login_username'])): ?>
                    <a href="careers.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center">
                        <i class="fas fa-briefcase mr-2"></i>Jobs
                    </a>
                    <a href="forum.php" class="nav-link text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300 flex items-center active">
                        <i class="fas fa-comments mr-2"></i>Forums
                    </a>
                    <div id="user-dropdown" class="relative">
                        <button id="account_settings" type="button" class="flex items-center space-x-2 text-white hover:text-red-300 px-4 py-2 rounded-lg transition-all duration-300">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span><?= htmlspecialchars($_SESSION['login_username']) ?></span>
                            <i class="fas fa-angle-down"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 glass-effect rounded-lg shadow-xl py-2 hidden" id="dropdown-menu">
                            <a href="admin/ajax.php?action=logout2" class="flex items-center px-4 py-2 text-gray-700 hover:bg-red-50 transition-colors duration-200">
                                <i class="fas fa-sign-out-alt mr-3"></i>Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <button onclick="openLoginModal()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                <?php endif; ?>
                </div>
            </nav>
            <nav id="mobile-nav" class="md:hidden absolute top-full left-0 right-0 glass-dark rounded-b-2xl shadow-xl hidden">
                <div class="px-6 py-4 space-y-4">
                    <a href="index.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                        <i class="fas fa-home mr-3"></i>Home
                    </a>
                    <a href="alumni_list.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                        <i class="fas fa-users mr-3"></i>Alumni
                    </a>
                    <a href="gallery.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                        <i class="fas fa-images mr-3"></i>Gallery
                    </a>
                    <div class="pt-4 border-t border-gray-600">
                        <?php if(isset($_SESSION['login_username'])): ?>
                            <a href="careers.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                                <i class="fas fa-briefcase mr-3"></i>Jobs
                            </a>
                            <a href="forum.php" class="block text-white hover:text-red-300 py-2 transition-colors duration-300 flex items-center">
                                <i class="fas fa-comments mr-3"></i>Forums
                            </a>
                            <button id="mobile-account_settings" type="button" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-user-circle mr-2"></i><?= htmlspecialchars($_SESSION['login_username']) ?>
                                <i class="fas fa-angle-down ml-2"></i>
                            </button>
                            <a href="admin/ajax.php?action=logout2" class="block text-center mt-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg border border-gray-200 transition">Logout</a>
                        <?php else: ?>
                            <button onclick="openLoginModal()" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</header>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function(){
        const nav = document.getElementById('mobile-nav');
        if(nav) nav.classList.toggle('hidden');
    });
    // User dropdown toggle
    document.getElementById('account_settings')?.addEventListener('click', function(e){
        e.preventDefault();
        const menu = document.getElementById('dropdown-menu');
        if(menu) menu.classList.toggle('hidden');
    });
    document.getElementById('mobile-account_settings')?.addEventListener('click', function(){
        const menu = document.getElementById('dropdown-menu');
        if(menu) menu.classList.toggle('hidden');
    });
</script>

<!-- Forum hero moved below navigation -->

<div class="container mx-auto max-w-4xl mt-24 pt-6 md:mt-28 md:pt-8 px-4">
    <div class="bg-white rounded-2xl shadow-xl mb-8">
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-end gap-4">
                <div class="flex-1 mb-3 md:mb-0">
                    <label for="filter" class="text-gray-500 mb-2 block">Search Topics</label>
                    <div class="flex rounded-xl overflow-hidden border-2 border-red-100">
                        <span class="flex items-center bg-red-100 px-4 text-red-600">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" class="flex-1 px-4 py-3 text-base focus:outline-none focus:ring focus:ring-red-200" id="filter" placeholder="Enter keywords to search..." aria-label="Filter">
                    </div>
                </div>
                <div class="w-full md:w-48">
                    <button class="bg-gradient-to-r from-red-600 to-red-400 text-white rounded-xl px-6 py-3 font-semibold shadow-lg w-full flex items-center justify-center gap-2 hover:from-red-800 hover:to-red-600 transition" id="search">
                        <i class="fa fa-search"></i>Search
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="forum-list">
        <?php
        $event = $conn->query("SELECT f.*,u.name from forum_topics f inner join users u on u.id = f.user_id order by f.id desc");
        if($event && $event->num_rows > 0):
            while($row = $event->fetch_assoc()):
                $trans = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
                unset($trans["\""], $trans["<"], $trans[">"], $trans["<h2"]);
                $desc = strtr(html_entity_decode($row['description']),$trans);
                $desc = str_replace(array("<li>","</li>"), array("",","), $desc);
                $count_comments = $conn->query("SELECT * FROM forum_comments where topic_id = ".$row['id'])->num_rows;
        ?>
    <div class="Forum-list bg-white rounded-2xl shadow-lg mb-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl fade-in relative" data-id="<?php echo $row['id'] ?>">
            <div class="p-8">
                <div>
                    <?php if(isset($_SESSION['login_id']) && $_SESSION['login_id'] == $row['user_id']): ?>
                    <div class="dropdown float-right">
                        <a class="text-gray-400 hover:text-red-600 transition" href="javascript:void(0)" id="dropdownMenuButton<?php echo $row['id']; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="fa fa-ellipsis-v"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right rounded-xl shadow-lg border-none mt-2" aria-labelledby="dropdownMenuButton<?php echo $row['id']; ?>">
                            <a class="dropdown-item edit_forum flex items-center gap-2 px-5 py-2 hover:bg-red-100 hover:text-red-600 transition" data-id="<?php echo $row['id'] ?>" href="javascript:void(0)">
                                <i class="fa fa-edit"></i>Edit Topic
                            </a>
                            <a class="dropdown-item delete_forum flex items-center gap-2 px-5 py-2 hover:bg-red-100 text-red-600 transition" data-id="<?php echo $row['id'] ?>" href="javascript:void(0)">
                                <i class="fa fa-trash"></i>Delete Topic
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <h3 class="text-red-800 text-2xl font-bold mb-2 filter-txt"><?php echo ucwords($row['title']) ?></h3>
                    
                    <div class="text-gray-500 mb-3 filter-txt truncate">
                        <?php echo strip_tags($desc) ?>
                    </div>

                    <hr class="border-red-100 my-3 border-2">

                    <div class="flex flex-wrap items-center justify-between">
                        <div class="flex flex-wrap items-center gap-2 mb-2 md:mb-0">
                            <span class="bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl px-4 py-2 flex items-center gap-1 font-semibold text-sm">
                                <i class="fa fa-user"></i>
                                <span class="filter-txt"><?php echo $row['name'] ?></span>
                            </span>
                            <span class="bg-gradient-to-r from-gray-500 to-gray-700 text-white rounded-xl px-4 py-2 flex items-center gap-1 font-semibold text-sm">
                                <i class="fa fa-comments"></i>
                                <?php echo $count_comments ?> Comment<?php echo $count_comments != 1 ? 's' : '' ?>
                            </span>
                        </div>
                        <button class="bg-gradient-to-r from-red-600 to-red-400 text-white rounded-xl px-6 py-2 font-semibold shadow-lg flex items-center gap-2 hover:from-red-800 hover:to-red-600 transition view_topic" data-id="<?php echo $row['id'] ?>">
                            <i class="fa fa-eye"></i>View Discussion
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; 
        else: ?>
        <div class="flex flex-col items-center justify-center py-12 text-gray-400 empty-state">
            <i class="fa fa-comments text-5xl mb-4 text-red-200"></i>
            <h4 class="text-lg font-bold mb-2">No Forum Topics Yet</h4>
            <p class="mb-4">Be the first to start a discussion!</p>
            <button class="bg-gradient-to-r from-red-600 to-red-400 text-white rounded-xl px-6 py-2 font-semibold shadow-lg flex items-center gap-2 hover:from-red-800 hover:to-red-600 transition" id="create_first_topic">
                <i class="fa fa-plus"></i>Create First Topic
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Start New Topic Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300" id="newForumModal" aria-labelledby="newForumModalLabel" role="dialog">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-auto overflow-hidden relative animate-fadeIn">
        <form id="newForumForm">
            <div class="bg-gradient-to-r from-red-600 to-red-500 text-white px-8 py-6 flex items-center justify-between">
                <h5 class="text-xl font-semibold flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Start New Topic
                </h5>
                <button type="button" class="text-white hover:bg-red-400 rounded-full w-10 h-10 flex items-center justify-center transition btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-8 py-6 bg-white">
                <div class="mb-6">
                    <label for="topicTitle" class="font-semibold text-gray-700 mb-2 block flex items-center gap-2">
                        <i class="fas fa-heading text-red-500"></i>Title
                    </label>
                    <input type="text" name="title" id="topicTitle" class="form-control border-2 border-red-100 rounded-xl px-4 py-3 text-base w-full focus:outline-none focus:ring focus:ring-red-200" 
                        placeholder="Enter your topic title..." required maxlength="250">
                    <div class="text-right text-sm mt-1 text-gray-400 char-counter">
                        <span id="titleCounter">0</span>/250 characters
                    </div>
                </div>
                <div class="mb-6">
                    <label for="topicDescription" class="font-semibold text-gray-700 mb-2 block flex items-center gap-2">
                        <i class="fas fa-align-left text-red-500"></i>Description
                    </label>
                    <textarea name="description" id="topicDescription" class="form-control border-2 border-red-100 rounded-xl px-4 py-3 text-base w-full focus:outline-none focus:ring focus:ring-red-200 min-h-[120px] resize-y"
                        rows="6" placeholder="Share your thoughts, questions, or ideas..." required></textarea>
                    <div class="text-right text-sm mt-1 text-gray-400 char-counter">
                        <span id="descCounter">0</span> characters
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-8 py-4 flex gap-2 justify-end rounded-b-2xl">
                <button type="submit" class="bg-gradient-to-r from-red-600 to-red-400 text-white rounded-xl px-6 py-2 font-semibold shadow-lg flex items-center gap-2 hover:from-red-800 hover:to-red-600 transition">
                    <i class="fas fa-paper-plane"></i>Create Topic
                </button>
                <button type="button" class="bg-gradient-to-r from-gray-500 to-gray-700 text-white rounded-xl px-6 py-2 font-semibold shadow flex items-center gap-2 hover:from-gray-700 hover:to-gray-900 transition btn-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Message Template -->
<div id="modalMessage" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm transition-all duration-300 modal-message">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-auto overflow-hidden relative message-container animate-fadeIn">
        <div class="py-8 px-8 text-center message-header">
            <div class="message-icon mb-4 flex items-center justify-center mx-auto w-20 h-20 rounded-full text-4xl bg-gradient-to-r from-gray-200 to-gray-100">
                <i class="message-icon-element"></i>
            </div>
            <h4 class="message-title text-xl font-bold mb-2"></h4>
            <p class="message-text text-gray-600 text-base"></p>
        </div>
        <div class="py-4 px-8 text-center message-footer">
            <button class="message-btn bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-full px-8 py-3 font-semibold shadow-lg transition hover:from-blue-700 hover:to-purple-700" onclick="hideMessage()">OK</button>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-100">
            <div class="progress-bar h-full w-0"></div>
        </div>
    </div>
</div>

<!-- Tailwind + jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom Animations (Tailwind can't do keyframes in CDN; add minimal inline CSS) -->
<style>
.fade-in { animation: fadeIn 0.5s ease-in; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px);} to { opacity: 1;transform: translateY(0);} }
.loading { opacity:0.6; pointer-events:none;}
.truncate { display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;}
</style>

<script>
// Modal Message Functions
function showMessage(type, title, message, autoClose = false, duration = 3000) {
    const modal = $('#modalMessage');
    const icon = modal.find('.message-icon-element');
    const titleEl = modal.find('.message-title');
    const messageEl = modal.find('.message-text');
    const progressBar = modal.find('.progress-bar');
    modal.removeClass('success error info loading');
    modal.addClass(type);
    // Set icon
    switch(type) {
        case 'success': icon.attr('class', 'fas fa-check message-icon-element'); break;
        case 'error': icon.attr('class', 'fas fa-times message-icon-element'); break;
        case 'info': icon.attr('class', 'fas fa-info message-icon-element'); break;
        case 'loading': icon.attr('class', 'fas fa-spinner fa-spin message-icon-element'); break;
    }
    titleEl.text(title);
    messageEl.text(message);
    modal.removeClass('hidden').addClass('flex');
    if (autoClose) {
        progressBar.css('transition', `width ${duration}ms linear`);
        setTimeout(() => { progressBar.css('width', '100%');}, 100);
        setTimeout(hideMessage, duration);
    } else { progressBar.css('width', '0%'); }
}
function hideMessage() {
    $('#modalMessage').addClass('hidden').removeClass('flex');
}
function showSuccessMessage() { showMessage('success', 'Success!', 'Your topic has been created successfully!', true, 3000);}
function showErrorMessage() { showMessage('error', 'Error!', 'Failed to create topic. Please try again.');}
function showInfoMessage() { showMessage('info', 'Information', 'This is an informational message with important details.');}
function showLoadingMessage() {
    showMessage('loading', 'Processing...', 'Please wait while we process your request.');
    setTimeout(() => { hideMessage(); setTimeout(showSuccessMessage, 300); }, 3000);
}

// Modal open/close helpers for Tailwind
function openModal(id) { $(id).removeClass('hidden').addClass('flex'); }
function closeModal(id) { $(id).addClass('hidden').removeClass('flex'); }

// jQuery code for UI
$(function(){
    // Show modal
    $('#new_forum, #create_first_topic').click(function(){ openModal('#newForumModal'); });
    $('.btn-close').click(function(){ closeModal('#newForumModal'); });

    // Form submission
    $('#newForumForm').submit(function(e){
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        showMessage('loading', 'Creating Topic...', 'Please wait while we create your topic.');
        $btn.prop('disabled', true).html('<span class="animate-spin inline-block w-5 h-5 border-2 border-red-200 border-t-white rounded-full mr-2"></span>Saving...');
        $.ajax({
            url: 'admin/ajax.php?action=save_forum',
            method: 'POST',
            data: $form.serialize(),
            success: function(resp){
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i>Create Topic');
                hideMessage();
                setTimeout(() => {
                    if(resp == 1){
                        closeModal('#newForumModal');
                        showMessage('success', 'Success!', 'Your topic has been created successfully!', true, 3000);
                        setTimeout(function(){ location.reload(); }, 3500);
                    } else {
                        showMessage('error', 'Creation Failed', 'Unable to create your topic. Please check your input and try again.');
                    }
                }, 300);
            },
            error: function(){
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i>Create Topic');
                hideMessage();
                setTimeout(() => {
                    showMessage('error', 'Connection Error', 'An error occurred while connecting to the server. Please check your connection and try again.');
                }, 300);
            }
        });
    });

    // View topic
    $('.view_topic').click(function(){
        var id = $(this).data('id');
        window.location.href = 'view_forum.php?id=' + id;
    });

    // Edit forum
    $('.edit_forum').click(function(){
        var id = $(this).data('id');
        // Replace with your modal open function
        window.location.href = "manage_forum.php?id=" + id;
    });

    // Delete forum
    $('.delete_forum').click(function(){
        var id = $(this).data('id');
        if(confirm("Are you sure you want to delete this topic? This action cannot be undone.")) {
            delete_forum(id);
        }
    });

    // Search functionality
    $('#filter').on('keypress', function(e){
        if(e.which == 13) { $('#search').trigger('click'); }
    });
    $('#search').click(function(){
        const txt = $('#filter').val().trim();
        const $forumItems = $('.Forum-list');
        $forumItems.addClass('loading');
        setTimeout(function() {
            if(txt === ''){
                $forumItems.show().removeClass('loading');
                clearHighlights();
                hideNoResults();
                return;
            }
            $forumItems.each(function(){
                const $this = $(this);
                let content = "";
                $this.find(".filter-txt").each(function(){
                    content += ' ' + $(this).text();
                });
                if(content.toLowerCase().includes(txt.toLowerCase())){
                    $this.show();
                    highlightSearchTerms($this, txt);
                } else {
                    $this.hide();
                }
            });
            $forumItems.removeClass('loading');
            const visibleCards = $('.Forum-list:visible').length;
            if(visibleCards === 0) { showNoResults(); } else { hideNoResults(); }
        }, 300);
    });
    $('#filter').on('input', function(){
        if($(this).val() === '') {
            $('.Forum-list').show();
            clearHighlights();
            hideNoResults();
        }
    });
});

// Modal close logic for clicking outside and Escape key
$(document).on('click', '#modalMessage', function(e) { if(e.target === this) hideMessage(); });
$(document).keyup(function(e) { if(e.keyCode === 27) hideMessage(); });

// Character counters and form validation
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('topicTitle');
    const descInput = document.getElementById('topicDescription');
    const titleCounter = document.getElementById('titleCounter');
    const descCounter = document.getElementById('descCounter');
    const form = document.getElementById('newForumForm');
    titleInput.addEventListener('input', function() {
        const length = this.value.length;
        titleCounter.textContent = length;
        titleCounter.parentElement.classList.toggle('text-red-500', length > 230);
    });
    descInput.addEventListener('input', function() { descCounter.textContent = this.value.length; });
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        const title = titleInput.value.trim();
        const description = descInput.value.trim();
        if (title.length < 5) {
            titleInput.classList.add('border-red-500');
            isValid = false;
        } else {
            titleInput.classList.remove('border-red-500');
            titleInput.classList.add('border-green-500');
        }
        if (description.length < 10) {
            descInput.classList.add('border-red-500');
            isValid = false;
        } else {
            descInput.classList.remove('border-red-500');
            descInput.classList.add('border-green-500');
        }
        if (isValid) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
            submitBtn.disabled = true;
            setTimeout(() => {
                alert('Topic created successfully!');
                closeModal('#newForumModal');
                form.reset();
                titleCounter.textContent = '0';
                descCounter.textContent = '0';
                titleInput.classList.remove('border-green-500');
                descInput.classList.remove('border-green-500');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 1500);
        }
    });
    document.getElementById('newForumModal').addEventListener('hidden.bs.modal', function() {
        form.reset();
        titleCounter.textContent = '0';
        descCounter.textContent = '0';
        titleInput.classList.remove('border-green-500', 'border-red-500');
        descInput.classList.remove('border-green-500', 'border-red-500');
    });
});

function delete_forum($id){
    $.ajax({
        url: 'admin/ajax.php?action=delete_forum',
        method: 'POST',
        data: {id: $id},
        success: function(resp){
            if(resp == 1){
                showMessage('success', 'Deleted!', 'Topic successfully deleted!', true, 2000);
                setTimeout(function(){ location.reload(); }, 2000);
            }
        },
        error: function(){ showMessage('error', 'Error!', 'An error occurred'); }
    });
}

// Highlight search terms
function highlightSearchTerms($element, searchText) {
    clearHighlights();
    const regex = new RegExp(`(${searchText})`, 'gi');
    $element.find('.filter-txt').each(function() {
        const $this = $(this);
        const text = $this.text();
        const highlightedText = text.replace(regex, '<span class="bg-yellow-100 font-semibold px-1 rounded highlight">$1</span>');
        $this.html(highlightedText);
    });
}
function clearHighlights() {
    $('.highlight').each(function() {
        const $this = $(this);
        $this.replaceWith($this.text());
    });
}
function showNoResults() {
    if($('#no-results').length === 0) {
        $('#forum-list').append(`
            <div id="no-results" class="flex flex-col items-center justify-center py-12 text-gray-400 empty-state">
                <i class="fa fa-search text-5xl mb-4 text-yellow-200"></i>
                <h4 class="text-lg font-bold mb-2">No Results Found</h4>
                <p class="mb-4">Try adjusting your search terms or browse all topics.</p>
                <button class="bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl px-6 py-2 font-semibold shadow-lg flex items-center gap-2 hover:from-blue-700 hover:to-blue-900 transition" onclick="$('#filter').val(''); $('#search').click();">
                    <i class="fa fa-refresh mr-2"></i>Show All Topics
                </button>
            </div>
        `);
    }
}
function hideNoResults() { $('#no-results').remove(); }
$('html').css('scroll-behavior', 'smooth');

// Modal Functions (copy from other pages with modals)
window.openLoginModal = function() {
    document.getElementById('loginModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
window.closeLoginModal = function() {
    document.getElementById('loginModal').classList.remove('show');
    document.body.style.overflow = 'auto';
}
window.openRegisterModal = function() {
    closeLoginModal();
    document.getElementById('registerModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
window.closeRegisterModal = function() {
    document.getElementById('registerModal').classList.remove('show');
    document.body.style.overflow = 'auto';
}
window.switchToLogin = function() {
    closeRegisterModal();
    openLoginModal();
}

// Close modals
document.addEventListener('click', function(e) {
    if (e.target.id === 'loginModal') closeLoginModal();
    if (e.target.id === 'registerModal') closeRegisterModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLoginModal();
        closeRegisterModal();
    }
});

// Populate Batch Years
function populateBatchYears() {
    const batchSelect = document.getElementById('batch_year_forum');
    if (!batchSelect) return;
    const currentYear = new Date().getFullYear();
    const startYear = 1950;
    batchSelect.innerHTML = '<option value="">-- Select Batch Year --</option>';
    for (let year = currentYear; year >= startYear; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = `${year} - ${year + 1}`;
        batchSelect.appendChild(option);
    }
}
document.addEventListener('DOMContentLoaded', populateBatchYears);

// Handle Login
async function handleLogin(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('username', document.getElementById('login_username').value);
    formData.append('password', document.getElementById('login_password').value);
    try {
        const response = await fetch('authenticate.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await response.json();
        if (data.status === 'success') {
            window.location.reload();
        } else if (data.type === 'unverified') {
            document.getElementById('unverifiedError').classList.remove('hidden');
            document.getElementById('loginError').classList.add('hidden');
        } else {
            document.getElementById('loginError').classList.remove('hidden');
            document.getElementById('unverifiedError').classList.add('hidden');
        }
    } catch (error) {
        document.getElementById('loginError').classList.remove('hidden');
    }
    return false;
}

// Handle Register
async function handleRegister(e) {
    e.preventDefault();
    const password = document.getElementById('reg_password').value;
    const confirm = document.getElementById('reg_confirm_password').value;
    if (password.length < 8) {
        alert('Password must be at least 8 characters long.');
        return false;
    }
    if (!/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
        alert('Password must contain at least one uppercase letter and one number.');
        return false;
    }
    if (password !== confirm) {
        alert('Passwords do not match.');
        return false;
    }
    const formData = new FormData(e.target);
    try {
        const response = await fetch('register_save.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const text = await response.text();
        if (text.includes('success') || text.includes('Registration Submitted') || text.includes('awaiting admin approval')) {
            document.getElementById('registerSuccess').classList.remove('hidden');
            e.target.reset();
            document.querySelector('#registerModal .modal-content').scrollTop = 0;
            setTimeout(() => {
                closeRegisterModal();
                window.location.href = 'index.php';
            }, 3000);
        } else if (text.includes('error') || text.includes('exists')) {
            alert('Registration failed: Username or email may already exist. Please try different credentials.');
        } else {
            alert('Registration failed. Please try again.');
        }
    } catch (error) {
        alert('Registration failed. Please try again.');
    }
    return false;
}
</script>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.modal-overlay.show {
    display: flex;
    opacity: 1;
}
.modal-content {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
    transform: scale(0.9) translateY(20px);
    transition: transform 0.3s ease;
    position: relative;
}
.modal-content-large {
    max-width: 900px;
}
.modal-overlay.show .modal-content {
    transform: scale(1) translateY(0);
}
.modal-content::-webkit-scrollbar {
    width: 6px;
}
.modal-content::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}
.modal-content::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    border-radius: 10px;
}
</style>

<!-- Login Modal -->
<div id="loginModal" class="modal-overlay">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button onclick="closeLoginModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-600 transition-colors z-10">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <div class="text-center mb-6">
            <span class="inline-block bg-gradient-to-br from-red-600 via-rose-600 to-pink-500 p-3 rounded-full mb-3 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M16 21v-2a4 4 0 0 0-8 0v2"/>
                </svg>
            </span>
            <h2 class="text-3xl font-bold text-red-700 mb-2">Alumni Network</h2>
            <p class="text-rose-500 font-medium">Sign in to your account</p>
        </div>
        <div id="loginError" class="hidden mb-4 bg-red-50 border border-red-300 text-red-700 font-semibold rounded-lg px-4 py-3 text-center shadow">
            Incorrect username or password.
        </div>
        <div id="unverifiedError" class="hidden mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 font-semibold rounded-lg px-4 py-3 shadow">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-bold">Account Not Verified</p>
                    <p class="text-sm mt-1">Your account is still awaiting admin approval. Please check back later or contact the administrator.</p>
                </div>
            </div>
        </div>
        <form id="loginForm" class="space-y-5" onsubmit="return handleLogin(event)">
            <div>
                <label for="login_username" class="block text-red-700 font-semibold mb-2">Username / Email</label>
                <input type="text" id="login_username" name="username" required
                    class="w-full px-4 py-3 rounded-lg border border-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 bg-red-50 text-gray-800 transition duration-200 shadow-sm" 
                    placeholder="Enter your username or email">
            </div>
            <div>
                <label for="login_password" class="block text-red-700 font-semibold mb-2">Password</label>
                <input type="password" id="login_password" name="password" required
                    class="w-full px-4 py-3 rounded-lg border border-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 bg-red-50 text-gray-800 transition duration-200 shadow-sm" 
                    placeholder="Enter your password">
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold py-3 rounded-lg shadow-lg hover:from-red-700 hover:to-rose-700 transition-all duration-300">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>
        <div class="mt-6 text-center">
            <span class="text-red-500">Don't have an account?</span>
            <button onclick="openRegisterModal()" class="ml-2 text-red-700 font-bold hover:text-rose-800 underline transition-all">Create Account</button>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div id="registerModal" class="modal-overlay">
    <div class="modal-content modal-content-large" onclick="event.stopPropagation()">
        <button onclick="closeRegisterModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-600 transition-colors z-10">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <div class="text-center mb-6">
            <span class="inline-block bg-gradient-to-r from-red-600 to-rose-600 p-3 rounded-full mb-3 shadow-lg">
                <i class="fas fa-user-plus text-white text-3xl"></i>
            </span>
            <h2 class="text-3xl font-bold text-red-700 mb-2">Create New Account</h2>
            <p class="text-rose-500 font-medium">Join the alumni network</p>
        </div>
        <div id="registerSuccess" class="hidden mb-4 bg-blue-50 border border-blue-300 text-blue-700 font-semibold rounded-lg px-4 py-3 shadow">
            <p class="font-bold mb-1">Registration Submitted!</p>
            <p class="text-sm">Your account is awaiting admin approval. You'll be able to login once verified.</p>
        </div>
        <form id="registerForm" class="space-y-4" onsubmit="return handleRegister(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="firstname" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Middle Name</label>
                    <input type="text" name="middlename" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="lastname" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Batch / School Year <span class="text-red-500">*</span></label>
                    <select name="batch" id="batch_year_forum" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
                        <option value="">-- Select Batch Year --</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select your graduation year</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Course <span class="text-red-500">*</span></label>
                    <select name="course_id" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-white">
                        <option value="">-- Choose Course --</option>
                        <?php 
                        $courses_query = $conn->query("SELECT id, course FROM courses ORDER BY course ASC");
                        if($courses_query):
                        while($course = $courses_query->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course']); ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Mobile <span class="text-red-500">*</span></label>
                    <input type="tel" name="mobile" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-1 text-sm">Address <span class="text-red-500">*</span></label>
                <textarea name="address" rows="2" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50"></textarea>
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-1 text-sm">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="reg_password" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                    <p class="text-xs text-gray-500 mt-1">Min 8 chars, 1 uppercase, 1 number</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-sm">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" name="confirm_password" id="reg_confirm_password" required class="w-full px-4 py-2 border border-red-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition shadow-sm bg-red-50" />
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-1 text-sm">Avatar (Optional)</label>
                <input type="file" name="img" accept="image/*" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-red-500 transition shadow-sm bg-white text-sm" />
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold py-3 rounded-lg shadow-lg hover:from-red-700 hover:to-rose-700 transition-all duration-300">
                <i class="fas fa-user-plus mr-2"></i>Create Account
            </button>
        </form>
        <div class="mt-4 text-center">
            <span class="text-red-500 text-sm">Already have an account?</span>
            <button onclick="switchToLogin()" class="ml-2 text-red-700 font-bold hover:text-rose-800 underline transition-all text-sm">Sign In</button>
        </div>
    </div>
</div>