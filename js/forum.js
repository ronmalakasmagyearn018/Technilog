/**
 * forum.js  —  Technilog Forum Backend Integration
 *
 * Drop this file into:  Technilog/js/forum.js
 * Then add at the bottom of forum.html (before </body>):
 *   <script src="../js/forum.js"></script>
 *
 * What this does:
 *  - Loads all posts on page load
 *  - Lets any logged-in user (including admin) create a post
 *  - Shows admin badge on posts made by admins
 *  - Clicking a username/avatar opens a profile modal (same info as account.html)
 *  - Clicking a post opens its comments inline
 *  - Admin / post author can delete their post
 */

(function () {
  "use strict";

  /* ─── CONFIG ─────────────────────────────────────────────────── */
  const BASE = "../backend/";           // adjust if your folder differs
  const IMG_BASE = "../";               // base for profile_pic paths

  /* ─── STATE ──────────────────────────────────────────────────── */
  let currentUser = null;               // populated after session check

  /* ─── BOOT ───────────────────────────────────────────────────── */
  document.addEventListener("DOMContentLoaded", () => {
    injectStyles();
    buildModal();
    buildCreatePostUI();
    checkSession().then(() => loadPosts());
  });

  /* ─── SESSION CHECK ──────────────────────────────────────────── */
  async function checkSession() {
    const uid  = localStorage.getItem('tl_user_id')    || '';
    const name = localStorage.getItem('tl_name')        || '';
    const email= localStorage.getItem('tl_user_email') || '';
    const role = localStorage.getItem('tl_role')        || 'user';
    if (uid) {
      currentUser = { user_id: uid, fullname: name, username: name, email, role };
    }
  }

  /* ─── LOAD POSTS ─────────────────────────────────────────────── */
  async function loadPosts() {
    const container = getPostContainer();
    if (!container) return;

    container.innerHTML = `<div class="forum-loading">Loading posts…</div>`;

    try {
      const res  = await fetch(BASE + "get_forum_posts.php");
      const data = await res.json();

      container.innerHTML = "";

      if (!data.success || !data.posts.length) {
        container.innerHTML = `<div class="forum-empty">No posts yet. Be the first to post!</div>`;
        return;
      }

      data.posts.forEach(post => container.appendChild(buildPostCard(post)));
    } catch (e) {
      container.innerHTML = `<div class="forum-error">Failed to load posts. Please refresh.</div>`;
    }
  }

  /* ─── BUILD POST CARD ────────────────────────────────────────── */
  function buildPostCard(post) {
    const isAdmin  = post.role === "admin";
    const isAuthor = currentUser && parseInt(currentUser.user_id) === parseInt(post.user_id);
    const canDelete = isAuthor || (currentUser && currentUser.role === "admin");

    const card = document.createElement("div");
    card.className = "forum-post-card";
    card.dataset.postId = post.post_id;

    const avatar = post.profile_pic
      ? `<img src="${IMG_BASE}${post.profile_pic}" class="forum-avatar" alt="avatar">`
      : `<div class="forum-avatar forum-avatar-default">${(post.fullname || post.username || "U")[0].toUpperCase()}</div>`;

    const mediaHtml = (post.media_path && post.media_type)
      ? renderMedia(post.media_path, post.media_type)
      : "";

    const tagLabel = post.tag
      ? `<span class="forum-post-tag forum-post-tag-${escHtml(post.tag)}">${escHtml(post.tag)}</span>`
      : "";

    const canReport = currentUser && currentUser.id && String(currentUser.id) !== String(post.user_id) && post.role !== "admin";

    card.innerHTML = `
      <div class="forum-post-header">
        <div class="forum-user-info">
          <span class="forum-avatar-wrap" data-user-id="${post.user_id}" title="View profile">${avatar}</span>
          <div class="forum-meta">
            <span class="forum-username" data-user-id="${post.user_id}">${escHtml(post.fullname || post.username)}</span>
            ${isAdmin ? `<span class="forum-badge admin-badge">Admin</span>` : ""}
            ${tagLabel}
            <span class="forum-date">${formatDate(post.created_at)}</span>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          ${canReport ? `<button class="forum-report-btn" data-post-id="${post.post_id}" data-user-id="${post.user_id}" title="Report post" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;padding:2px 6px;">⚑</button>` : ""}
          ${canDelete ? `<button class="forum-delete-btn" data-post-id="${post.post_id}" title="Delete post">&#128465;</button>` : ""}
        </div>
      </div>
      <div class="forum-post-body">
        <h3 class="forum-post-title">${escHtml(post.title)}</h3>
        <p class="forum-post-content">${escHtml(post.content)}</p>
        ${mediaHtml}
      </div>
      <div class="forum-post-footer">
        <button class="forum-like-btn" data-post-id="${post.post_id}" style="background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;color:#6b7280;font-size:14px;">
          <span class="forum-like-icon">🤍</span>
          <span class="forum-like-count">${post.like_count || 0}</span>
        </button>
        <button class="forum-comments-toggle" data-post-id="${post.post_id}">
          💬 ${post.comment_count} Comment${post.comment_count == 1 ? "" : "s"}
        </button>
      </div>
      <div class="forum-comments-section" id="comments-${post.post_id}" style="display:none;"></div>
    `;

    // Click username / avatar → profile modal
    card.querySelectorAll("[data-user-id]").forEach(el => {
      el.addEventListener("click", () => openProfileModal(post.user_id));
    });

    // Toggle comments
    card.querySelector(".forum-comments-toggle").addEventListener("click", (e) => {
      e.stopPropagation();
      toggleComments(post.post_id, card);
    });

    // Delete post
    const delBtn = card.querySelector(".forum-delete-btn");
    if (delBtn) {
      delBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        deletePost(post.post_id, card);
      });
    }

    // Report post
    const repBtn = card.querySelector(".forum-report-btn");
    if (repBtn) {
      repBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        openReportModal(post.post_id, post.user_id);
      });
    }

    // Like post
    const likeBtn = card.querySelector(".forum-like-btn");
    if (likeBtn) {
      likeBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        likePost(post.post_id);
      });
    }

    return card;
  }

  /* ─── COMMENTS ───────────────────────────────────────────────── */
  async function toggleComments(postId, card) {
    const section = card.querySelector(`#comments-${postId}`);
    const btn     = card.querySelector(".forum-comments-toggle");

    if (section.style.display !== "none") {
      section.style.display = "none";
      return;
    }

    section.style.display = "block";
    section.innerHTML     = `<div class="forum-loading">Loading comments…</div>`;

    try {
      const res  = await fetch(`${BASE}get_forum_comments.php?post_id=${postId}`);
      const data = await res.json();

      section.innerHTML = "";

      if (data.comments && data.comments.length) {
        data.comments.forEach(c => section.appendChild(buildComment(c)));
      } else {
        section.innerHTML = `<div class="forum-empty-comments">No comments yet.</div>`;
      }

      // Comment input box
      if (currentUser) {
        section.appendChild(buildCommentForm(postId, section, btn));
      }
    } catch (e) {
      section.innerHTML = `<div class="forum-error">Failed to load comments.</div>`;
    }
  }

  function buildComment(c) {
    const isAdmin = c.role === "admin";
    const wrap = document.createElement("div");
    wrap.className = "forum-comment";

    const avatar = c.profile_pic
      ? `<img src="${IMG_BASE}${c.profile_pic}" class="forum-avatar forum-avatar-sm" alt="avatar">`
      : `<div class="forum-avatar forum-avatar-sm forum-avatar-default">${(c.fullname || c.username || "U")[0].toUpperCase()}</div>`;

    wrap.innerHTML = `
      <span class="forum-avatar-wrap" data-user-id="${c.user_id}" title="View profile">${avatar}</span>
      <div class="forum-comment-body">
        <div class="forum-comment-meta">
          <span class="forum-username" data-user-id="${c.user_id}">${escHtml(c.fullname || c.username)}</span>
          ${isAdmin ? `<span class="forum-badge admin-badge">Admin</span>` : ""}
          <span class="forum-date">${formatDate(c.created_at)}</span>
        </div>
        <div class="forum-comment-text" style="text-align:left;width:fit-content;max-width:100%;margin:0;font-size:.88rem;line-height:1.5;white-space:pre-wrap;word-break:break-word;">${escHtml(c.content)}</div>
      </div>
    `;

    wrap.querySelectorAll("[data-user-id]").forEach(el => {
      el.addEventListener("click", () => openProfileModal(c.user_id));
    });

    return wrap;
  }

  function buildCommentForm(postId, section, countBtn) {
    const form = document.createElement("div");
    form.className = "forum-comment-form";
    form.innerHTML = `
      <textarea class="forum-comment-input" placeholder="Write a comment…" rows="2"></textarea>
      <button class="forum-submit-comment-btn">Reply</button>
    `;

    form.querySelector(".forum-submit-comment-btn").addEventListener("click", async () => {
      const input   = form.querySelector(".forum-comment-input");
      const content = input.value.trim();
      if (!content) return;

      const fd = new FormData();
      fd.append("post_id", postId);
      fd.append("content", content);
      fd.append("user_id", currentUser?.user_id || '');

      try {
        const res  = await fetch(BASE + "create_forum_comment.php", { method: "POST", body: fd });
        const data = await res.json();
        if (data.success) {
          input.value = "";
          // Reload comments
          const res2  = await fetch(`${BASE}get_forum_comments.php?post_id=${postId}`);
          const data2 = await res2.json();
          // Remove old comments (keep form)
          section.querySelectorAll(".forum-comment").forEach(el => el.remove());
          const emptyMsg = section.querySelector(".forum-empty-comments");
          if (emptyMsg) emptyMsg.remove();
          if (data2.comments && data2.comments.length) {
            data2.comments.forEach(c => section.insertBefore(buildComment(c), form));
            // Update count button
            countBtn.textContent = `💬 ${data2.comments.length} Comment${data2.comments.length == 1 ? "" : "s"}`;
          }
        } else {
          alert(data.message || "Failed to post comment.");
        }
      } catch (e) {
        alert("Error submitting comment.");
      }
    });

    return form;
  }

  /* ─── CREATE POST UI ─────────────────────────────────────────── */
  function buildCreatePostUI() {
    const container = getPostContainer();
    if (!container) return;

    const wrap = document.createElement("div");
    wrap.id        = "forum-create-post";
    wrap.className = "forum-create-post";
    wrap.innerHTML = `
      <h3 class="forum-create-title">Create a Post</h3>
      <input  type="text"     id="forum-post-title"   class="forum-input" placeholder="Post title…" maxlength="255">
      <textarea               id="forum-post-content" class="forum-input forum-textarea" placeholder="What\'s on your mind?" rows="4"></textarea>
      <label class="forum-media-label">📷 Attach up to 5 photos <span id="forum-img-counter" class="forum-img-counter">(0 / 5)</span></label>
      <input type="file" id="forum-post-media" class="forum-media-input" accept="image/*" multiple>
      <div id="forum-media-preview" class="forum-media-preview forum-media-grid"></div>
      <button id="forum-post-submit" class="forum-submit-btn">Post</button>
      <p id="forum-post-msg" class="forum-msg" style="display:none;"></p>
    `;

    // Insert before the posts list
    container.parentNode.insertBefore(wrap, container);

    document.getElementById("forum-post-submit").addEventListener("click", submitPost);

    // Multi-image preview (max 5)
    const MAX_IMAGES = 5;
    let selectedFiles = [];

    document.getElementById("forum-post-media").addEventListener("change", function () {
      const newFiles = Array.from(this.files).filter(f => f.type.startsWith("image/"));
      const remaining = MAX_IMAGES - selectedFiles.length;
      if (this.files.length > remaining) {
        const msg = document.getElementById("forum-post-msg");
        showMsg(msg, `You can only attach up to ${MAX_IMAGES} photos. ${MAX_IMAGES - remaining} already selected.`, "error");
      }
      selectedFiles = selectedFiles.concat(newFiles.slice(0, remaining));
      this.value = "";
      renderImagePreviews();
    });

    function renderImagePreviews() {
      const preview = document.getElementById("forum-media-preview");
      const counter = document.getElementById("forum-img-counter");
      counter.textContent = `(${selectedFiles.length} / ${MAX_IMAGES})`;
      preview.innerHTML = "";

      selectedFiles.forEach((file, idx) => {
        const url = URL.createObjectURL(file);
        const thumb = document.createElement("div");
        thumb.className = "forum-preview-thumb";
        thumb.innerHTML = `<img src="${url}" class="forum-preview-img" alt="photo ${idx + 1}"><button class="forum-remove-media" title="Remove" type="button">✕</button>`;
        thumb.querySelector(".forum-remove-media").addEventListener("click", () => {
          selectedFiles.splice(idx, 1);
          renderImagePreviews();
        });
        preview.appendChild(thumb);
      });

      if (selectedFiles.length < MAX_IMAGES) {
        const addSlot = document.createElement("label");
        addSlot.className = "forum-preview-add";
        addSlot.htmlFor = "forum-post-media";
        addSlot.innerHTML = `<span>+</span><small>Add photo</small>`;
        preview.appendChild(addSlot);
      }

      wrap._selectedFiles = selectedFiles;
    }

    renderImagePreviews();
  }

    async function submitPost() {
    const title   = document.getElementById("forum-post-title").value.trim();
    const content = document.getElementById("forum-post-content").value.trim();
    const msg     = document.getElementById("forum-post-msg");
    const btn     = document.getElementById("forum-post-submit");

    if (!title || !content) {
      showMsg(msg, "Please fill in both the title and content.", "error");
      return;
    }

    btn.disabled   = true;
    btn.textContent = "Posting…";

    const createWrap = document.getElementById("forum-create-post");
    const images = (createWrap && createWrap._selectedFiles) ? createWrap._selectedFiles : [];

    const fd = new FormData();
    fd.append("title",   title);
    fd.append("content", content);
    fd.append("user_id", currentUser?.user_id || '');
    images.forEach((file, i) => fd.append(`images[${i}]`, file));

    try {
      const res  = await fetch(BASE + "create_forum_post.php", { method: "POST", body: fd });
      const data = await res.json();

      if (data.success) {
        document.getElementById("forum-post-title").value   = "";
        document.getElementById("forum-post-content").value = "";
        if (createWrap) { createWrap._selectedFiles = []; }
        const renderFn = createWrap && createWrap._renderFn;
        if (typeof renderFn === "function") renderFn();
        else {
          const preview = document.getElementById("forum-media-preview");
          const counter = document.getElementById("forum-img-counter");
          if (preview) preview.innerHTML = "";
          if (counter) counter.textContent = "(0 / 5)";
        }
        showMsg(msg, "Post created!", "success");
        loadPosts();
      } else {
        showMsg(msg, data.message || "Failed to create post.", "error");
      }
    } catch (e) {
      showMsg(msg, "Network error. Please try again.", "error");
    } finally {
      btn.disabled    = false;
      btn.textContent = "Post";
    }
  }

  /* ─── DELETE POST ─────────────────────────────────────────────── */
  async function deletePost(postId, card) {
    if (!confirm("Delete this post? This cannot be undone.")) return;

    const fd = new FormData();
    fd.append("post_id", postId);

    try {
      const res  = await fetch(BASE + "delete_forum_post.php", { method: "POST", body: fd });
      const data = await res.json();
      if (data.success) {
        card.remove();
      } else {
        alert(data.message || "Failed to delete post.");
      }
    } catch (e) {
      alert("Network error.");
    }
  }

  /* ─── PROFILE MODAL ──────────────────────────────────────────── */
  function buildModal() {
    const modal = document.createElement("div");
    modal.id        = "forum-profile-modal";
    modal.className = "forum-modal-overlay";
    modal.innerHTML = `
      <div class="forum-modal-box">
        <button class="forum-modal-close" id="forum-modal-close">&times;</button>
        <div class="forum-modal-content" id="forum-modal-body">
          <div class="forum-loading">Loading profile…</div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);

    document.getElementById("forum-modal-close").addEventListener("click", closeModal);
    modal.addEventListener("click", e => { if (e.target === modal) closeModal(); });
  }

  async function openProfileModal(userId) {
    const modal = document.getElementById("forum-profile-modal");
    const body  = document.getElementById("forum-modal-body");
    modal.style.display = "flex";
    body.innerHTML = `<div class="forum-loading">Loading profile…</div>`;

    try {
      const res  = await fetch(`${BASE}get_forum_user.php?user_id=${userId}`);
      const data = await res.json();

      if (!data.success) {
        body.innerHTML = `<div class="forum-error">User not found.</div>`;
        return;
      }

      const u = data.user;
      const isAdmin = u.role === "admin";
      const avatar = u.profile_pic
        ? `<img src="${IMG_BASE}${u.profile_pic}" class="forum-modal-avatar" alt="avatar">`
        : `<div class="forum-modal-avatar forum-avatar-default">${(u.fullname || u.username || "U")[0].toUpperCase()}</div>`;

      // Gender display helper
      const genderIcon = u.gender === 'Male' ? '♂️' : u.gender === 'Female' ? '♀️' : '';

      body.innerHTML = `
        <div class="forum-modal-avatar-wrap">
          ${avatar}
          ${isAdmin ? `<span class="forum-badge admin-badge forum-badge-lg">Admin</span>` : ""}
        </div>
        ${u.bio ? `<p class="forum-modal-bio">${escHtml(u.bio)}</p>` : ""}
        <table class="forum-profile-table">
          <tr><th>Full Name</th>  <td>${escHtml(u.fullname  || "—")}</td></tr>
          <tr><th>Username</th>   <td>${escHtml(u.username  || "—")}</td></tr>
          ${u.gender ? `<tr><th>Gender</th><td>${genderIcon} ${escHtml(u.gender)}</td></tr>` : ""}
          <tr><th>Email</th>      <td>${escHtml(u.email     || "—")}</td></tr>
          ${u.address ? `<tr><th>Address</th><td>${escHtml(u.address)}</td></tr>` : ""}
        </table>
      `;
    } catch (e) {
      body.innerHTML = `<div class="forum-error">Failed to load profile.</div>`;
    }
  }

  function closeModal() {
    document.getElementById("forum-profile-modal").style.display = "none";
  }

  /* ─── HELPERS ─────────────────────────────────────────────────── */
  function getPostContainer() {
    // Try common selectors — adjust to match your forum.html structure
    return (
      document.getElementById("forum-posts-container") ||
      document.querySelector(".forum-posts")            ||
      document.querySelector(".forum-list")             ||
      document.querySelector(".forum-container .posts") ||
      document.querySelector("main")                    ||
      document.querySelector(".main-content")
    );
  }

  function renderMedia(path, type) {
    // ── NEW: 'mixed' type = JSON array of {path, type} objects (from create_forum_post.php) ──
    if (type === "mixed") {
      let items = [];
      try { items = JSON.parse(path); } catch(e) { return ""; }
      if (!Array.isArray(items) || items.length === 0) return "";

      const imageItems = items.filter(i => i.type === "image");
      const videoItems = items.filter(i => i.type === "video");
      let html = "";

      // Render images in a responsive grid
      if (imageItems.length > 0) {
        const visibleImgs = imageItems.slice(0, 4);
        const extraCount  = imageItems.length - visibleImgs.length;
        const gridClass   = `forum-post-media-grid forum-post-media-grid-${Math.min(visibleImgs.length, 3)}`;
        const imgs = visibleImgs.map((item, idx) => {
          const u = IMG_BASE + item.path;
          const isLast = idx === visibleImgs.length - 1 && extraCount > 0;
          return isLast
            ? `<div class="forum-post-media-extra-wrap" style="position:relative;cursor:pointer;" onclick="window.open('${u}','_blank')">
                <img src="${u}" class="forum-post-media-img" alt="post image" style="filter:brightness(0.45);">
                <span class="forum-post-media-extra-label">+${extraCount + 1}</span>
               </div>`
            : `<img src="${u}" class="forum-post-media-img" alt="post image" onclick="window.open('${u}','_blank')">`;
        }).join("");
        html += `<div class="${gridClass}">${imgs}</div>`;
      }

      // Render videos below images
      videoItems.forEach(item => {
        const u = IMG_BASE + item.path;
        html += `<video class="forum-post-media-video" controls preload="metadata"><source src="${u}"><p>Your browser does not support video.</p></video>`;
      });

      return html;
    }

    // ── LEGACY: 'images' type = JSON array of plain path strings ──
    if (type === "images") {
      let paths = [];
      try { paths = JSON.parse(path); } catch(e) { paths = [path]; }
      if (!Array.isArray(paths) || paths.length === 0) return "";
      if (paths.length === 1) {
        const url = IMG_BASE + paths[0];
        return `<div class="forum-post-media-grid forum-post-media-grid-1"><img src="${url}" class="forum-post-media-img" alt="post image" onclick="window.open('${url}','_blank')"></div>`;
      }
      const imgs = paths.map(p => {
        const u = IMG_BASE + p;
        return `<img src="${u}" class="forum-post-media-img" alt="post image" onclick="window.open('${u}','_blank')">`;
      }).join("");
      const gridClass = `forum-post-media-grid forum-post-media-grid-${Math.min(paths.length, 3)}`;
      return `<div class="${gridClass}">${imgs}</div>`;
    }

    // ── LEGACY: single image or video ──
    const url = IMG_BASE + path;
    if (type === "image") {
      return `<div class="forum-post-media-grid forum-post-media-grid-1"><img src="${url}" class="forum-post-media-img" alt="post image" onclick="window.open('${url}','_blank')"></div>`;
    } else if (type === "video") {
      return `<video class="forum-post-media-video" controls preload="metadata"><source src="${url}"><p>Your browser does not support video.</p></video>`;
    }
    return "";
  }

  function escHtml(str) {
    const d = document.createElement("div");
    d.appendChild(document.createTextNode(str || ""));
    return d.innerHTML;
  }

  function formatDate(dtStr) {
    if (!dtStr) return "";
    const d = new Date(dtStr);
    return d.toLocaleDateString("en-PH", { year: "numeric", month: "short", day: "numeric", hour: "2-digit", minute: "2-digit" });
  }

  function showMsg(el, text, type) {
    el.textContent    = text;
    el.style.display  = "block";
    el.style.color    = type === "error" ? "#e53935" : "#43a047";
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.display = "none"; }, 4000);
  }

  /* ─── STYLES ─────────────────────────────────────────────────── */
  // These are injected so we never touch style.css or any existing file
  function injectStyles() {
    const style = document.createElement("style");
    style.textContent = `
      /* ── Forum Post Container ── */
      #forum-create-post {
        background: var(--card-bg, #fff);
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
      }
      .forum-create-title {
        margin: 0 0 12px;
        font-size: 1rem;
        font-weight: 600;
      }
      .forum-input {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: .95rem;
        margin-bottom: 10px;
        font-family: inherit;
        resize: vertical;
        outline: none;
        transition: border-color .2s;
      }
      .forum-input:focus { border-color: var(--primary, #1a73e8); }
      .forum-textarea { min-height: 80px; }
      /* ── Multi-Image Upload ── */
      .forum-media-label {
        display: block;
        font-size: .85rem;
        color: #555;
        margin-bottom: 6px;
        cursor: pointer;
      }
      .forum-img-counter {
        font-size: .8rem;
        color: var(--primary, #1a73e8);
        font-weight: 600;
      }
      .forum-media-input {
        display: none;
      }
      .forum-media-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
      }
      .forum-preview-thumb {
        position: relative;
        width: 90px;
        height: 90px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e0e0e0;
        flex-shrink: 0;
      }
      .forum-preview-thumb .forum-preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
      }
      .forum-remove-media {
        position: absolute;
        top: 3px;
        right: 3px;
        background: rgba(0,0,0,.6);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 11px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        padding: 0;
      }
      .forum-preview-add {
        width: 90px;
        height: 90px;
        border: 2px dashed #bbb;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #aaa;
        font-size: .75rem;
        gap: 4px;
        transition: border-color .2s, color .2s;
        flex-shrink: 0;
      }
      .forum-preview-add:hover { border-color: var(--primary, #1a73e8); color: var(--primary, #1a73e8); }
      .forum-preview-add span { font-size: 1.5rem; line-height: 1; }
      /* ── Post media grid (display) ── */
      .forum-post-media-grid {
        display: grid;
        gap: 4px;
        margin: 8px 0;
        border-radius: 8px;
        overflow: hidden;
      }
      .forum-post-media-grid-1 { grid-template-columns: 1fr; }
      .forum-post-media-grid-2 { grid-template-columns: 1fr 1fr; }
      .forum-post-media-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
      .forum-post-media-grid .forum-post-media-img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        cursor: pointer;
        display: block;
        transition: opacity .15s;
      }
      .forum-post-media-grid-1 .forum-post-media-img { height: 300px; }
      .forum-post-media-grid .forum-post-media-img:hover { opacity: .9; }
      .forum-post-media-extra-wrap { position: relative; overflow: hidden; border-radius: 8px; }
      .forum-post-media-extra-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
      .forum-post-media-extra-label {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; font-weight: 700; color: #fff; pointer-events: none;
        text-shadow: 0 2px 8px rgba(0,0,0,.5);
      }
      .forum-submit-btn {
        background: var(--primary, #1a73e8);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 22px;
        font-size: .95rem;
        font-weight: 600;
        cursor: pointer;
        transition: opacity .2s;
      }
      .forum-submit-btn:disabled { opacity: .5; cursor: default; }
      .forum-msg { font-size: .88rem; margin-top: 6px; }

      /* ── Post Cards ── */
      .forum-post-card {
        background: var(--card-bg, #fff);
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,.07);
      }
      .forum-post-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
      }
      .forum-user-info { display: flex; align-items: center; gap: 10px; }
      .forum-meta { display: flex; flex-direction: column; }

      /* ── Avatar ── */
      .forum-avatar-wrap { cursor: pointer; display: inline-flex; }
      .forum-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
      }
      .forum-avatar-sm { width: 32px; height: 32px; }
      .forum-avatar-default {
        background: var(--primary, #1a73e8);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
      }
      .forum-avatar-sm.forum-avatar-default { font-size: .85rem; }

      /* ── Username & date ── */
      .forum-username {
        font-weight: 600;
        font-size: .95rem;
        cursor: pointer;
        color: inherit;
        text-decoration: none;
      }
      .forum-username:hover { text-decoration: underline; }
      .forum-date { font-size: .78rem; color: #888; margin-top: 2px; }

      /* ── Admin badge ── */
      .forum-badge {
        display: inline-block;
        font-size: .7rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: .04em;
        line-height: 1.6;
        vertical-align: middle;
        margin-left: 4px;
      }
      .admin-badge { background: #e53935; color: #fff; }
      .forum-badge-lg { font-size: .8rem; padding: 4px 12px; }

      /* ── Post content ── */
      .forum-post-title {
        margin: 0 0 6px;
        font-size: 1.05rem;
        font-weight: 600;
      }
      .forum-post-content {
        margin: 0;
        font-size: .92rem;
        line-height: 1.55;
        white-space: pre-wrap;
        word-break: break-word;
      }

      /* ── Footer / toggle ── */
      .forum-post-footer { margin-top: 12px; }
      .forum-comments-toggle {
        background: none;
        border: 1px solid #ddd;
        border-radius: 999px;
        padding: 5px 14px;
        font-size: .85rem;
        cursor: pointer;
        color: inherit;
        transition: background .15s;
      }
      .forum-comments-toggle:hover { background: rgba(0,0,0,.05); }

      /* ── Delete btn ── */
      .forum-delete-btn {
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        opacity: .5;
        padding: 2px 6px;
        border-radius: 6px;
        transition: opacity .15s, background .15s;
      }
      .forum-delete-btn:hover { opacity: 1; background: rgba(229,57,53,.1); }

      /* ── Comments section ── */
      .forum-comments-section {
        margin-top: 14px;
        border-top: 1px solid #eee;
        padding-top: 14px;
      }
      .forum-comment {
        display: flex;
        gap: 10px;
        margin-bottom: 12px;
      }
      .forum-comment-body { flex: 1; min-width: 0; }
      .forum-comment-meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 3px; }
      .forum-comment-text {
        margin: 0 !important;
        font-size: .88rem;
        line-height: 1.5;
        white-space: pre-wrap;
        word-break: break-word;
        text-align: left !important;
        width: fit-content !important;
        max-width: 100% !important;
        display: block !important;
      }
      .forum-comment-form { margin-top: 12px; display: flex; flex-direction: column; gap: 8px; }
      .forum-comment-input {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: .88rem;
        font-family: inherit;
        resize: vertical;
        outline: none;
      }
      .forum-comment-input:focus { border-color: var(--primary, #1a73e8); }
      .forum-submit-comment-btn {
        align-self: flex-end;
        background: var(--primary, #1a73e8);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 7px 18px;
        font-size: .88rem;
        font-weight: 600;
        cursor: pointer;
      }

      /* ── Loading / empty / error ── */
      .forum-loading, .forum-empty, .forum-error, .forum-empty-comments {
        text-align: center;
        padding: 20px;
        font-size: .9rem;
        color: #888;
      }
      .forum-error { color: #e53935; }

      /* ── Profile Modal ── */
      .forum-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
      }
      .forum-modal-box {
        background: var(--card-bg, #fff);
        border-radius: 16px;
        padding: 32px 28px 28px;
        width: min(420px, 92vw);
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 8px 32px rgba(0,0,0,.22);
      }
      .forum-modal-close {
        position: absolute;
        top: 14px;
        right: 16px;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        line-height: 1;
        color: #888;
      }
      .forum-modal-avatar-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
      }
      .forum-modal-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        font-size: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
      }
      .forum-profile-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .93rem;
      }
      .forum-profile-table th,
      .forum-profile-table td {
        padding: 8px 10px;
        text-align: left;
        border-bottom: 1px solid #eee;
      }
      .forum-modal-bio {
        font-size: 0.88rem;
        color: #555;
        font-style: italic;
        text-align: center;
        margin: -6px 0 12px;
        padding: 0 8px;
        line-height: 1.5;
      }
      .forum-profile-table th { font-weight: 600; color: #555; width: 38%; }
    `;
    document.head.appendChild(style);
  }
})();