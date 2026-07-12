$(document).ready(function() {
    // count how many cards we have and show it
    const totalStudents = $(".card").length;
    $("#student-count").text(totalStudents);
    
    // animation to fade in card one by one when page loads
    $(".card").each(function(index) {
        $(this).delay(index * 120).fadeIn({
            duration: 650,
            easing: 'swing'
        });
    });

    // hover effect for card background glow
    // calculate mouse pos on card
    $(".card").on("mousemove", function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        this.style.setProperty("--x", `${x}px`);
        this.style.setProperty("--y", `${y}px`);
    });

    // toggle detail section when button clicked
    $(".toggle-details-btn").on("click", function() {
        // console.log("clicked button!");
        const $btn = $(this);
        const $card = $btn.closest(".card");
        const $details = $card.find(".details");
        
        // toggle details with animation
        $details.slideToggle(350);
        
        $btn.toggleClass("expanded");
        
        // change button styling depending on state
        if ($btn.hasClass("expanded")) {
            $btn.text("Hide Details");
            $btn.css({
                "background-color": "#e11d48", /* rose red for hide */
                "box-shadow": "0 4px 12px rgba(225, 29, 72, 0.35)"
            });
        } else {
            $btn.text("Show Details");
            $btn.css({
                "background-color": "var(--primary-color)", /* go back to original Indigo */
                "box-shadow": "0 4px 12px rgba(99, 102, 241, 0.2)"
            });
        }
    });

    // search and filter stuff
    function filterStudents() {
        const query = $("#search-input").val().toLowerCase().trim();
        const activeFilter = $(".filter-btn.active").attr("data-filter");
        // console.log("filtering... query: " + query + ", filter: " + activeFilter);
        
        let visibleCount = 0;
        
        $(".card").each(function() {
            const $card = $(this);
            const name = $card.attr("data-name") || "";
            const major = $card.attr("data-major") || "";
            const level = $card.attr("data-level") || "";
            const email = $card.find(".detail-value").first().text().toLowerCase() || "";
            
            // matches query?
            const matchesSearch = query === "" || 
                                  name.includes(query) || 
                                  major.includes(query) || 
                                  email.includes(query);
                                  
            // matches category?
            const matchesFilter = activeFilter === "all" || level === activeFilter;
            
            if (matchesSearch && matchesFilter) {
                visibleCount++;
                if ($card.css("display") === "none") {
                    $card.stop(true, true).fadeIn(300);
                }
            } else {
                if ($card.css("display") !== "none") {
                    $card.stop(true, true).fadeOut(300);
                }
            }
        });
        
        // update count number
        $("#student-count").text(visibleCount);
        
        // show no results text if visible count is 0
        if (visibleCount === 0) {
            $("#no-results").stop(true, true).fadeIn(300);
        } else {
            $("#no-results").stop(true, true).fadeOut(150);
        }
    }

    // event handlers for search input typing
    $("#search-input").on("input", function() {
        const val = $(this).val();
        if (val.length > 0) {
            $("#clear-search").fadeIn(150);
        } else {
            $("#clear-search").fadeOut(150);
        }
        filterStudents();
    });

    // clear search button
    $("#clear-search").on("click", function() {
        $("#search-input").val("");
        $(this).fadeOut(150);
        filterStudents();
        $("#search-input").focus();
    });

    // filter buttons click
    $(".filter-btn").on("click", function() {
        $(".filter-btn").removeClass("active");
        $(this).addClass("active");
        filterStudents();
    });
});
