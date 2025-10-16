<?php 
include 'admin/db_connect.php'; 
?>

<style>
/* Modern red theme variables */
:root {
    --primary-red: #dc2626;
    --secondary-red: #ef4444;
    --light-red: #fef2f2;
    --dark-red: #991b1b;
    --accent-red: #f87171;
    --gradient-red: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    --gradient-light: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
    --shadow-red: rgba(220, 38, 38, 0.2);
    --text-dark: #1f2937;
    --text-light: #6b7280;
    --white: #ffffff;
}

/* Global styles */
body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Enhanced masthead */
.masthead {
    background: var(--gradient-red);
    position: relative;
    overflow: hidden;
    padding: 80px 0 60px;
    box-shadow: 0 10px 30px var(--shadow-red);
}

.masthead::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><polygon points="36,34 6,34 6,6 36,6"/></g></g></svg>');
    opacity: 0.3;
}

.masthead .container-fluid {
    position: relative;
    z-index: 2;
}

.masthead h3 {
    font-size: 3rem;
    font-weight: 700;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    margin-bottom: 1rem;
    letter-spacing: -0.025em;
}

.masthead .divider {
    max-width: 120px;
    border-top: 3px solid rgba(255, 255, 255, 0.8);
    margin: 2rem auto;
}

/* Search and filter section */
.filter-section {
    background: var(--white);
    padding: 30px 0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 40px;
    border-radius: 0 0 20px 20px;
}

.search-container {
    max-width: 600px;
    margin: 0 auto;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 15px 50px 15px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 50px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #f9fafb;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-red);
    background: var(--white);
    box-shadow: 0 0 0 3px var(--shadow-red);
}

.search-icon {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
}

/* Main content area */
.events-container {
    padding: 0 15px 60px;
}

.section-title {
    text-align: center;
    margin-bottom: 50px;
}

.section-title h4 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 15px;
    position: relative;
}

.section-title .subtitle {
    font-size: 1.1rem;
    color: var(--text-light);
    margin-bottom: 30px;
}

.section-divider {
    width: 80px;
    height: 4px;
    background: var(--gradient-red);
    border: none;
    border-radius: 2px;
    margin: 0 auto;
}

/* Modern event cards */
.event-list {
    background: var(--white);
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    margin-bottom: 30px;
    border: none;
    cursor: pointer;
    display: flex;
    position: relative;
}

.event-list:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

.event-list::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-red);
    z-index: 1;
}

/* Enhanced banner section */
.banner {
    width: 40%;
    position: relative;
    overflow: hidden;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-list:hover .banner img {
    transform: scale(1.05);
}

.banner::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(220, 38, 38, 0.1), transparent);
    pointer-events: none;
}

/* Enhanced card body */
.card-body {
    width: 60%;
    padding: 40px;
    display: flex;
    align-items: center;
}

.event-content {
    width: 100%;
}

.event-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 15px;
    line-height: 1.3;
}

.event-date {
    display: flex;
    align-items: center;
    color: var(--primary-red);
    font-weight: 600;
    margin-bottom: 20px;
    font-size: 1rem;
}

.event-date i {
    margin-right: 8px;
    font-size: 1.1rem;
}

.event-description {
    color: var(--text-light);
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 25px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Enhanced read more button */
.read-more-btn {
    background: var(--gradient-red);
    color: var(--white);
    border: none;
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 15px var(--shadow-red);
}

.read-more-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px var(--shadow-red);
    color: var(--white);
    text-decoration: none;
}

.read-more-btn i {
    transition: transform 0.3s ease;
}

.read-more-btn:hover i {
    transform: translateX(3px);
}

/* No events message */
.no-events {
    text-align: center;
    padding: 80px 20px;
    background: var(--white);
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    margin-top: 40px;
}

.no-events-icon {
    font-size: 4rem;
    color: var(--accent-red);
    margin-bottom: 20px;
}

.no-events h5 {
    color: var(--text-dark);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.no-events p {
    color: var(--text-light);
    font-size: 1.1rem;
}

/* Highlighting for search */
.highlight {
    background: linear-gradient(120deg, #fef3c7 0%, #fbbf24 100%);
    padding: 2px 4px;
    border-radius: 4px;
    font-weight: 600;
}

/* Responsive design */
@media (max-width: 992px) {
    .event-list {
        flex-direction: column;
    }
    
    .banner, .card-body {
        width: 100%;
    }
    
    .banner {
        min-height: 250px;
    }
    
    .card-body {
        padding: 30px 25px;
    }
    
    .masthead h3 {
        font-size: 2.2rem;
    }
    
    .section-title h4 {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .masthead {
        padding: 60px 0 40px;
    }
    
    .masthead h3 {
        font-size: 1.8rem;
    }
    
    .card-body {
        padding: 25px 20px;
    }
    
    .event-title {
        font-size: 1.5rem;
    }
    
    .banner {
        min-height: 200px;
    }
    
    .search-input {
        font-size: 14px;
        padding: 12px 45px 12px 18px;
    }
}

/* Loading animation */
.events-container.loading::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-red);
    animation: loading 2s infinite;
    z-index: 9999;
}

@keyframes loading {
    0% { transform: translateX(-100%); }
    50% { transform: translateX(0%); }
    100% { transform: translateX(100%); }
}

/* Smooth entrance animation */
.event-list {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

.event-list:nth-child(even) {
    animation-delay: 0.1s;
}

.event-list:nth-child(odd) {
    animation-delay: 0.2s;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- <header class="masthead">
    <div class="container-fluid h-100">
        <div class="row h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-8 align-self-end mb-4 page-title">
                <h3 class="text-white">Welcome to <?php echo $_SESSION['system']['name']; ?></h3>
                <hr class="divider my-4" />
            </div>
        </div>
    </div>
</header> -->

<div class="filter-section">
    <div class="container">
        <div class="search-container">
            <input type="text" id="filter" class="search-input" placeholder="Search events by title or description...">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
</div>

<div class="container events-container">
    <div class="section-title">
        <h4>Upcoming Events</h4>
        <p class="subtitle">Discover amazing events happening near you</p>
        <hr class="section-divider">
    </div>
    
    <div class="events-grid">
        <?php
        $event = $conn->query("SELECT * FROM events where date_format(schedule,'%Y-%m-%d') >= '".date('Y-m-d')."' order by unix_timestamp(schedule) asc");
        if($event && $event->num_rows > 0):
            while($row = $event->fetch_assoc()):
                $trans = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
                unset($trans["\""], $trans["<"], $trans[">"] , $trans["<h2"]);
                $desc = strtr(html_entity_decode($row['content']),$trans);
                $desc = str_replace(array("<li>","</li>"), array("",","), $desc);
        ?>
        <div class="card event-list" data-id="<?php echo $row['id'] ?>">
            <div class='banner'>
                <?php if(!empty($row['banner'])): ?>
                    <img src="admin/assets/uploads/<?php echo($row['banner']) ?>" alt="<?php echo htmlspecialchars($row['title']) ?>" loading="lazy">
                <?php else: ?>
                    <div style="background: var(--gradient-light); display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                        <i class="fas fa-calendar-alt" style="font-size: 3rem; color: var(--primary-red); opacity: 0.5;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="event-content">
                    <h3 class="event-title">
                        <span class="filter-txt"><?php echo ucwords($row['title']) ?></span>
                    </h3>
                    <div class="event-date">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date("F d, Y", strtotime($row['schedule'])) ?> at <?php echo date("h:i A", strtotime($row['schedule'])) ?>
                    </div>
                    <p class="event-description filter-txt">
                        <?php echo strip_tags($desc) ?>
                    </p>
                    <button class="read-more-btn read_more" data-id="<?php echo $row['id'] ?>">
                        Read More
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <div class="no-events">
            <div class="no-events-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h5>No Upcoming Events</h5>
            <p>There are no upcoming events at the moment. Check back later for exciting new events!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Enhanced read more functionality
    $('.read_more').click(function(e) {
        e.stopPropagation();
        const eventId = $(this).attr('data-id');
        
        // Add loading state
        const originalText = $(this).html();
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        // Small delay for better UX
        setTimeout(() => {
            location.href = "index.php?page=view_event&id=" + eventId;
        }, 300);
    });
    
    // Enhanced image viewer
    $('.banner img').click(function(e) {
        e.stopPropagation();
        const imgSrc = $(this).attr('src');
        viewer_modal(imgSrc);
    });
    
    // Enhanced search functionality
    $('#filter').on('input', function() {
        const filter = $(this).val().toLowerCase().trim();
        let visibleCount = 0;
        
        $('.card.event-list').each(function() {
            const $card = $(this);
            const title = $card.find('.event-title .filter-txt').text().toLowerCase();
            const description = $card.find('.event-description').text().toLowerCase();
            const isMatch = title.includes(filter) || description.includes(filter);
            
            if (filter === '' || isMatch) {
                $card.show().removeClass('filtered-out');
                visibleCount++;
                
                // Highlight matching text
                if (filter !== '') {
                    highlightText($card.find('.filter-txt'), filter);
                } else {
                    removeHighlight($card.find('.filter-txt'));
                }
            } else {
                $card.hide().addClass('filtered-out');
            }
        });
        
        // Show/hide no results message
        toggleNoResultsMessage(visibleCount === 0 && filter !== '');
    });
    
    // Highlight matching text
    function highlightText($elements, searchTerm) {
        $elements.each(function() {
            const $element = $(this);
            const text = $element.text();
            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
            const highlightedText = text.replace(regex, '<span class="highlight">$1</span>');
            $element.html(highlightedText);
        });
    }
    
    // Remove highlighting
    function removeHighlight($elements) {
        $elements.each(function() {
            const $element = $(this);
            const text = $element.text(); // This removes HTML tags
            $element.text(text);
        });
    }
    
    // Escape special regex characters
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    // Show/hide no results message
    function toggleNoResultsMessage(show) {
        const $noResults = $('#no-results-message');
        
        if (show && $noResults.length === 0) {
            const noResultsHtml = `
                <div id="no-results-message" class="no-events">
                    <div class="no-events-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h5>No Events Found</h5>
                    <p>Try adjusting your search terms to find what you're looking for.</p>
                </div>
            `;
            $('.events-grid').append(noResultsHtml);
        } else if (!show && $noResults.length > 0) {
            $noResults.remove();
        }
    }
    
    // Clear search functionality
    $('#filter').on('keydown', function(e) {
        if (e.key === 'Escape') {
            $(this).val('').trigger('input');
        }
    });
    
    // Add smooth scrolling for better UX
    $('.event-list').click(function(e) {
        if (!$(e.target).hasClass('read_more') && !$(e.target).closest('.read_more').length) {
            const eventId = $(this).attr('data-id');
            
            // Add a subtle click effect
            $(this).addClass('clicked');
            setTimeout(() => {
                $(this).removeClass('clicked');
            }, 200);
            
            // Navigate after effect
            setTimeout(() => {
                location.href = "index.php?page=view_event&id=" + eventId;
            }, 150);
        }
    });
    
    // Add loading class for any AJAX operations
    function showLoading() {
        $('.events-container').addClass('loading');
    }
    
    function hideLoading() {
        $('.events-container').removeClass('loading');
    }
});

// Add click effect styles
const clickStyles = `
<style>
.event-list.clicked {
    transform: translateY(-2px) scale(0.98);
    transition: all 0.1s ease;
}
</style>
`;
$('head').append(clickStyles);
</script>