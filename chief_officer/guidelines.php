<?php
require_once '../officer_auth.php';
if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') die('Access denied');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guidelines | Chief Officer Dashboard</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/Guide lines.css">
  <link rel="shortcut icon" href="../favlogo.png" type="image/x-icon">

  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
    .guidelines-page{padding:20px;}
    .g-grid{display:grid;grid-template-columns:1fr;gap:16px;}
    .g-card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.08);border:1px solid #eee;}
    .g-head{padding:16px 18px;border-bottom:1px solid #eee;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
    .g-title{font-size:18px;font-weight:700;color:#1f2a37;}
    .g-desc{margin-top:6px;color:#6b7280;font-size:13px;line-height:1.5;}
    .g-meta{margin-top:10px;font-size:12px;color:#94a3b8;display:flex;gap:12px;flex-wrap:wrap;}
    .g-body{padding:14px 18px 18px;}
    .g-block{padding:12px 12px;border:1px solid #eee;border-radius:12px;margin-top:12px;background:#fafafa;}
    .g-block h4{font-size:14px;font-weight:700;color:#111827;margin-bottom:8px;}
    .g-block p{font-size:13px;color:#374151;line-height:1.7;white-space:pre-line;}
    .g-actions{display:flex;gap:10px;align-items:center;}
    .btnx{border:none;border-radius:10px;padding:9px 12px;font-weight:700;cursor:pointer;font-size:13px}
    .btnx-primary{background:#2563eb;color:#fff;}
    .btnx-outline{background:#fff;border:1px solid #d1d5db;color:#111827;}
    .badge{padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700}
    .b-active{background:#dcfce7;color:#166534;}
    .b-draft{background:#fff7ed;color:#9a3412;}
    .b-archived{background:#f1f5f9;color:#475569;}

    /* Modal */
    .modalX{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;padding:16px;z-index:999;}
    .modalX .panel{width:980px;max-width:100%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,.25);}
    .panel-head{padding:14px 16px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;}
    .panel-head h3{font-size:16px;font-weight:800;}
    .panel-body{padding:16px;max-height:70vh;overflow:auto;}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .fg{margin-bottom:12px;}
    .fg label{display:block;font-size:12px;color:#6b7280;margin-bottom:6px;font-weight:700;}
    .fg input,.fg select,.fg textarea{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:12px;font-size:14px;}
    .block-editor{border:1px solid #e5e7eb;border-radius:14px;padding:12px;margin-top:12px;}
    .block-editor-top{display:flex;gap:10px;align-items:center;justify-content:space-between;margin-bottom:10px;}
    .block-editor-top strong{font-size:13px;}
    .btnx-danger{background:#ef4444;color:#fff;}
    .panel-foot{padding:14px 16px;border-top:1px solid #eee;display:flex;gap:10px;justify-content:flex-end;}
  </style>
</head>
<body>

<div class="app-container">
  <!-- Sidebar (keep your main sidebar layout) -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <i class="fas fa-hands-helping"></i>
        <div>
          <div style="font-size:1.3rem;">Family Bridge</div>
          <div class="admin-tag">Chief Officer Portal</div>
        </div>
      </div>
    </div>

    <div class="admin-info">
      <div class="admin-avatar"><i class="fas fa-user-shield"></i></div>
      <div class="admin-name">Chief Officer</div>
      <div class="admin-role">System Administrator</div>
    </div>

    <nav class="sidebar-nav">
      <a href="index.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      <a href="users.php" class="nav-item"><i class="fas fa-users"></i><span>User Management</span></a>
      <a href="children-management.php" class="nav-item"><i class="fas fa-child"></i><span>Children Management</span></a>
      <a href="clients.php" class="nav-item"><i class="fas fa-user-friends"></i><span>Clients</span></a>
      <a href="appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i><span>Appointments</span></a>
      <a href="Inquires.php" class="nav-item"><i class="fas fa-question-circle"></i><span>Inquiries</span></a>
      <a href="guidelines.php" class="nav-item active"><i class="fas fa-book"></i><span>Guidelines</span></a>
    </nav>

    <div class="logout-section">
      <button class="logout-btn" id="logoutBtn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
    </div>
  </aside>

  <main class="main-content">
    <header class="header">
      <div class="header-left">
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <div class="page-title">
          <h1>Adoption Guidelines</h1>
          <p>Manage and update adoption policies, procedures, and guidelines</p>
        </div>
      </div>
      <div class="header-right">
        <div class="admin-profile">
          <div class="admin-avatar-sm">CO</div>
          <div class="admin-info-sm"><h4>Chief Officer</h4><p>Guidelines Management</p></div>
        </div>
      </div>
    </header>

    <div class="content guidelines-page">
      <div class="page-header" style="margin-bottom:12px;">
        <div>
          <h1>Adoption Guidelines & Policies</h1>
          <p>Only the main 5 sections are shown here. Each section is editable.</p>
        </div>
      </div>

      <div class="g-grid" id="guidelinesGrid">
        <div class="g-card"><div class="g-head"><div><div class="g-title">Loading...</div></div></div></div>
      </div>
    </div>
  </main>
</div>

<!-- EDIT MODAL -->
<div class="modalX" id="editModal">
  <div class="panel">
    <div class="panel-head">
      <h3 id="modalHeading">Edit Guideline</h3>
      <button class="btnx btnx-outline" id="closeModalBtn"><i class="fas fa-times"></i></button>
    </div>

    <div class="panel-body">
      <input type="hidden" id="g_id">
      <input type="hidden" id="g_section_key">

      <div class="row">
        <div class="fg">
          <label>Section Title</label>
          <input type="text" id="g_title" placeholder="e.g., Eligibility & Requirements">
        </div>
        <div class="fg">
          <label>Status</label>
          <select id="g_status">
            <option value="active">active</option>
            <option value="draft">draft</option>
            <option value="archived">archived</option>
          </select>
        </div>
      </div>

      <div class="fg">
        <label>Section Description</label>
        <input type="text" id="g_description" placeholder="Short description for this section">
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
        <strong style="font-size:13px;color:#111827;">Sub-headings + description blocks</strong>
        <button class="btnx btnx-outline" id="addBlockBtn"><i class="fas fa-plus"></i> Add Block</button>
      </div>

      <div id="blocksWrap"></div>
    </div>

    <div class="panel-foot">
      <button class="btnx btnx-outline" id="cancelBtn">Cancel</button>
      <button class="btnx btnx-primary" id="saveBtn"><i class="fas fa-save"></i> Save</button>
    </div>
  </div>
</div>

<script>
const API_LOAD = "api/load_guidelines.php";
const API_SAVE = "api/save_guideline.php";

const MAIN_SECTIONS = [
  { section_key:"eligibility",     icon:"fa-user-check" },
  { section_key:"application",     icon:"fa-file-contract" },
  { section_key:"legal",           icon:"fa-balance-scale" },
  { section_key:"child_welfare",   icon:"fa-child" },
  { section_key:"international",   icon:"fa-globe-americas" }
];

let guidelines = []; // loaded from DB

const grid = document.getElementById('guidelinesGrid');

// modal fields
const editModal = document.getElementById('editModal');
const g_id = document.getElementById('g_id');
const g_section_key = document.getElementById('g_section_key');
const g_title = document.getElementById('g_title');
const g_description = document.getElementById('g_description');
const g_status = document.getElementById('g_status');
const blocksWrap = document.getElementById('blocksWrap');

document.getElementById('logoutBtn').addEventListener('click', ()=>{
  if(confirm("Logout?")) window.location.href = "../officer_logout.php";
});

document.getElementById('closeModalBtn').addEventListener('click', closeModal);
document.getElementById('cancelBtn').addEventListener('click', closeModal);

document.getElementById('addBlockBtn').addEventListener('click', ()=>{
  addBlockEditor({heading:"", text:""});
});

document.getElementById('saveBtn').addEventListener('click', saveGuideline);

function openModal(){
  editModal.style.display = "flex";
}
function closeModal(){
  editModal.style.display = "none";
}

// UI helpers
function statusBadgeClass(s){
  if(s === 'draft') return 'b-draft';
  if(s === 'archived') return 'b-archived';
  return 'b-active';
}

function esc(s){
  return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function safeJsonParse(str, fallback){
  try { return JSON.parse(str); } catch(e) { return fallback; }
}

// build blocks editor
function addBlockEditor(block){
  const wrapper = document.createElement('div');
  wrapper.className = 'block-editor';

  wrapper.innerHTML = `
    <div class="block-editor-top">
      <strong>Block</strong>
      <button type="button" class="btnx btnx-danger"><i class="fas fa-trash"></i> Remove</button>
    </div>
    <div class="fg">
      <label>Sub-heading</label>
      <input type="text" class="b_heading" value="${esc(block.heading)}" placeholder="e.g., Basic Requirements">
    </div>
    <div class="fg">
      <label>Description</label>
      <textarea class="b_text" rows="6" placeholder="Write details here...">${esc(block.text)}</textarea>
    </div>
  `;

  wrapper.querySelector('.btnx-danger').addEventListener('click', ()=> wrapper.remove());
  blocksWrap.appendChild(wrapper);
}

function readBlocksFromEditor(){
  const list = [];
  blocksWrap.querySelectorAll('.block-editor').forEach(el=>{
    const heading = el.querySelector('.b_heading').value.trim();
    const text = el.querySelector('.b_text').value.trim();
    if(heading !== "" || text !== ""){
      list.push({heading, text});
    }
  });
  return list;
}

// render cards (only 5)
function render(){
  const map = Object.fromEntries(guidelines.map(g => [g.section_key, g]));

  grid.innerHTML = MAIN_SECTIONS.map(s=>{
    const g = map[s.section_key];

    const title = g?.title || s.section_key;
    const description = g?.description || 'No description yet.';
    const status = g?.status || 'active';
    const updated = g?.updated_at ? new Date(g.updated_at).toLocaleDateString() : '-';

    let blocks = [];
    if (g?.content_json) {
      // if API returns content_json as string
      blocks = typeof g.content_json === 'string' ? safeJsonParse(g.content_json, []) : (g.content_json || []);
    }

    const blocksHtml = (blocks.length ? blocks : [{heading:"", text:"No content yet."}]).map(b=>`
      <div class="g-block">
        <h4>${esc(b.heading || ' ')}</h4>
        <p>${esc(b.text || '')}</p>
      </div>
    `).join('');

    return `
      <div class="g-card">
        <div class="g-head">
          <div>
            <div class="g-title"><i class="fas ${s.icon}" style="margin-right:8px;color:#2563eb;"></i>${esc(title)}</div>
            <div class="g-desc">${esc(description)}</div>
            <div class="g-meta">
              <span><i class="fas fa-calendar-alt"></i> Last Updated: ${esc(updated)}</span>
              <span class="badge ${statusBadgeClass(status)}">${esc(status)}</span>
            </div>
          </div>
          <div class="g-actions">
            <button class="btnx btnx-primary" data-edit="${esc(s.section_key)}"><i class="fas fa-edit"></i> Edit</button>
          </div>
        </div>
        <div class="g-body">${blocksHtml}</div>
      </div>
    `;
  }).join('');

  // attach edit handlers
  grid.querySelectorAll('[data-edit]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const key = btn.getAttribute('data-edit');
      openEditor(key);
    });
  });
}

function openEditor(section_key){
  const g = guidelines.find(x => x.section_key === section_key) || null;

  g_id.value = g?.id || '';
  g_section_key.value = section_key;
  g_title.value = g?.title || '';
  g_description.value = g?.description || '';
  g_status.value = g?.status || 'active';

  blocksWrap.innerHTML = '';

  let blocks = [];
  if(g?.content_json){
    blocks = typeof g.content_json === 'string' ? safeJsonParse(g.content_json, []) : (g.content_json || []);
  }

  if(!blocks.length){
    blocks = [{heading:"", text:""}];
  }

  blocks.forEach(b=> addBlockEditor(b));
  document.getElementById('modalHeading').textContent = `Edit: ${section_key}`;

  openModal();
}

async function loadAll(){
  const res = await fetch(API_LOAD);
  const data = await res.json();

  if(!data.success){
    alert(data.message || "Failed to load guidelines");
    return;
  }

  // EXPECT: items include section_key, title, description, content_json, status, updated_at
  guidelines = data.items || [];
  render();
}

async function saveGuideline(){
  const section_key = g_section_key.value.trim();
  const id = g_id.value ? parseInt(g_id.value, 10) : 0;

  const title = g_title.value.trim();
  const description = g_description.value.trim();
  const status = g_status.value;
  const blocks = readBlocksFromEditor();

  if(!section_key || !title || !description){
    alert("Please fill Title and Description.");
    return;
  }
  if(!blocks.length){
    alert("Please add at least one block (sub-heading + description).");
    return;
  }

  const form = new FormData();
  if(id > 0) form.append("id", id);
  form.append("section_key", section_key);
  form.append("title", title);
  form.append("description", description);
  form.append("status", status);
  form.append("content_json", JSON.stringify(blocks)); // stored as JSON in DB

  const res = await fetch(API_SAVE, {method:"POST", body: form});
  const data = await res.json();

  alert(data.message || (data.success ? "Saved" : "Failed"));
  if(data.success){
    closeModal();
    await loadAll();
  }
}

document.addEventListener('DOMContentLoaded', loadAll);
</script>

</body>
</html>
