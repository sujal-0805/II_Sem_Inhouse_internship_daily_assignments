// where we get the data from
const CONFIG = {
  API_URL: 'https://jsonplaceholder.typicode.com/posts',
  SKELETON_COUNT: 9
};

// store posts here
const state = {
  posts: [],
  filteredPosts: [],
  searchQuery: '',
  theme: 'light'
};

// get elements by id
const DOM = {
  postsContainer: document.getElementById('posts-container'),
  searchInput: document.getElementById('search-input'),
  countBadgeText: document.getElementById('count-badge-text'),
  countBadgeTextMobile: document.getElementById('count-badge-text-mobile'),
  themeToggleBtn: document.getElementById('theme-toggle'),
  themeIcon: document.getElementById('theme-icon'),
  errorContainer: document.getElementById('error-container'),
  errorMessage: document.getElementById('error-message'),
  retryBtn: document.getElementById('retry-btn'),
  modalTitle: document.getElementById('postDetailModalLabel'),
  modalUserBadge: document.getElementById('modal-user-badge'),
  modalPostBody: document.getElementById('modal-post-body'),
  detailModal: null
};

// page load
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initEventListeners();
  initModal();
  loadData();
});

// setup modal for detail view
function initModal() {
  DOM.detailModal = new bootstrap.Modal(document.getElementById('postDetailModal'));
}

// theme check light or dark
function initTheme() {
  const savedTheme = localStorage.getItem('theme');
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  if (savedTheme) {
    setTheme(savedTheme);
  } else if (systemPrefersDark) {
    setTheme('dark');
  } else {
    setTheme('light');
  }
}

// set theme
function setTheme(themeName) {
  state.theme = themeName;
  document.body.setAttribute('data-theme', themeName);
  localStorage.setItem('theme', themeName);
  
  if (themeName === 'dark') {
    DOM.themeIcon.className = 'bi bi-sun';
  } else {
    DOM.themeIcon.className = 'bi bi-moon-stars';
  }
}

// theme toggle
function toggleTheme() {
  const newTheme = state.theme === 'light' ? 'dark' : 'light';
  setTheme(newTheme);
}

// listeners
function initEventListeners() {
  DOM.themeToggleBtn.addEventListener('click', toggleTheme);
  
  // search field input
  DOM.searchInput.addEventListener('input', handleSearch);
  
  // retry connection
  DOM.retryBtn.addEventListener('click', () => {
    hideError();
    loadData();
  });
}

// escape regex for search highlight
function escapeRegExp(str) {
  // got this from stackoverflow for escaping regex
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// highlight search term inside title
function highlightText(text, search) {
  if (!search.trim()) return text;
  const escapedSearch = escapeRegExp(search.trim());
  const regex = new RegExp(`(${escapedSearch})`, 'gi');
  return text.replace(regex, '<mark class="highlight">$1</mark>');
}

// show loading animation skeletons
function renderSkeletonLoaders() {
  DOM.postsContainer.innerHTML = '';
  
  for (let i = 0; i < CONFIG.SKELETON_COUNT; i++) {
    const skeletonHTML = `
      <div class="col skeleton-card">
        <div class="post-card skeleton-shimmer">
          <div class="card-body">
            <div class="skeleton-title bg-body-tertiary"></div>
            <div class="skeleton-text bg-body-tertiary"></div>
            <div class="skeleton-text bg-body-tertiary w-75"></div>
            <div class="skeleton-text bg-body-tertiary w-50"></div>
          </div>
          <div class="card-footer-custom">
            <div class="skeleton-badge bg-body-tertiary"></div>
            <div class="skeleton-btn bg-body-tertiary"></div>
          </div>
        </div>
      </div>
    `;
    DOM.postsContainer.insertAdjacentHTML('beforeend', skeletonHTML);
  }
  
  updateCountersText('Loading posts...');
}

// update text on count badge
function updateCountersText(txt) {
  DOM.countBadgeText.textContent = txt;
  DOM.countBadgeTextMobile.textContent = txt;
}

// calculate count
function updateCountBadge() {
  const showing = state.filteredPosts.length;
  const total = state.posts.length;
  updateCountersText('Showing ' + showing + ' of ' + total + ' posts');
}

// main api load
async function loadData() {
  DOM.searchInput.disabled = true;
  renderSkeletonLoaders();
  
  try {
    const res = await fetch(CONFIG.API_URL);
    
    if (!res.ok) {
      throw new Error(`HTTP Error status: ${res.status}`);
    }
    
    const data = await res.json();
    
    // console.log("Fetched posts:", data);
    
    if (!Array.isArray(data)) {
      throw new Error("Invalid format received. Expected JSON Array of posts.");
    }
    
    state.posts = data;
    state.filteredPosts = data;
    
    renderPosts();
    DOM.searchInput.disabled = false;
    
  } catch (err) {
    console.error("Fetch operation failed:", err);
    showError(err.message || "Failed to communicate with JSONPlaceholder API.");
  }
}

// render post cards list
function renderPosts() {
  DOM.postsContainer.innerHTML = '';
  
  if (state.filteredPosts.length === 0) {
    renderNoResultsMessage();
    updateCountBadge();
    return;
  }
  
  state.filteredPosts.forEach(post => {
    const colDiv = document.createElement('div');
    colDiv.className = 'col';
    
    const displayedTitle = highlightText(post.title, state.searchQuery);
    
    colDiv.innerHTML = `
      <div class="post-card">
        <div class="card-body">
          <h3 class="card-title text-capitalize" title="${post.title}">${displayedTitle}</h3>
          <p class="card-text">${post.body}</p>
        </div>
        <div class="card-footer-custom">
          <span class="user-badge">
            <i class="bi bi-person-fill"></i> User ID: ${post.userId}
          </span>
          <button class="btn-read-more" data-id="${post.id}">
            Read Post <i class="bi bi-arrow-right-short"></i>
          </button>
        </div>
      </div>
    `;
    
    const readBtn = colDiv.querySelector('.btn-read-more');
    readBtn.addEventListener('click', () => {
      openDetailModal(post);
    });
    
    DOM.postsContainer.appendChild(colDiv);
  });
  
  updateCountBadge();
}

// show message if no matches
function renderNoResultsMessage() {
  DOM.postsContainer.innerHTML = `
    <div class="col-12 text-center py-5">
      <div class="mb-3 text-secondary">
        <i class="bi bi-search-heart fs-1"></i>
      </div>
      <h3 class="h5 fw-bold text-secondary-emphasis display-font">No matches found</h3>
      <p class="text-secondary small max-width-300 mx-auto">No post titles matched your search query "${state.searchQuery}". Try editing your keyword or clear search filters.</p>
      <button id="clear-search-btn" class="btn btn-primary rounded-3 btn-sm mt-2 px-3">
        Clear Filter
      </button>
    </div>
  `;
  
  document.getElementById('clear-search-btn').addEventListener('click', () => {
    DOM.searchInput.value = '';
    state.searchQuery = '';
    state.filteredPosts = state.posts;
    renderPosts();
  });
}

// filtering when user types in search box
function handleSearch(e) {
  const query = e.target.value.toLowerCase().trim();
  state.searchQuery = query;
  
  if (!query) {
    state.filteredPosts = state.posts;
  } else {
    state.filteredPosts = state.posts.filter(p => 
      p.title.toLowerCase().includes(query)
    );
  }
  
  renderPosts();
}

// show bootstrap modal
function openDetailModal(post) {
  DOM.modalTitle.textContent = post.title;
  DOM.modalUserBadge.innerHTML = `<i class="bi bi-person-fill me-1"></i> User ID: ${post.userId}`;
  DOM.modalPostBody.textContent = post.body;
  
  DOM.detailModal.show();
}

// show error msg on screen
function showError(msg) {
  DOM.postsContainer.innerHTML = '';
  DOM.errorMessage.textContent = `${msg}. Check your local internet connection, DNS configuration, and refresh page to retry.`;
  DOM.errorContainer.classList.remove('d-none');
  DOM.errorContainer.classList.add('d-flex');
  updateCountersText('Failed to download posts');
}

// hide error banner
function hideError() {
  DOM.errorContainer.classList.remove('d-flex');
  DOM.errorContainer.classList.add('d-none');
}
