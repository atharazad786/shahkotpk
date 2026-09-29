<?php
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';

$me = require_permission('dashboard.view'); // Minimum admin permission

// Handle AJAX AI request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_content') {
    header('Content-Type: application/json');
    try {
        require_once __DIR__.'/../app/ai_orchestrator_v710.php';
        
        $name = trim($_POST['name'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        $tone = trim($_POST['tone'] ?? 'Professional');
        $module = trim($_POST['module'] ?? 'businesses');
        
        $provider = ai710_provider();
        if (!$provider) throw new Exception('No active AI provider configured in the Command Center.');
        
        $prompt = "You are a professional SEO copywriter. Generate content for a '$module' listing.\n";
        $prompt .= "Name/Title: $name\nKeywords/Features: $keywords\nTone: $tone\n\n";
        $prompt .= "Return EXACTLY a raw JSON object with no markdown fences, formatted like this:\n";
        $prompt .= "{\n  \"title\": \"SEO optimized title here (max 60 chars)\",\n  \"description\": \"SEO meta description (max 160 chars)\",\n  \"body\": \"Full description body...\"\n}";

        $res = ai710_provider_call($provider, [
            ['role' => 'system', 'content' => 'Always respond with valid JSON.'],
            ['role' => 'user', 'content' => $prompt]
        ], ['max_tokens' => 800, 'temperature' => 0.6]);

        $raw = trim($res['text']);
        if (strpos($raw, '```json') !== false) {
            $raw = preg_replace('/```json|```/', '', $raw);
        }
        $json = json_decode(trim($raw), true);

        if (!$json || !isset($json['title'])) {
            throw new Exception("AI returned malformed response.");
        }

        echo json_encode(['success' => true, 'data' => $json]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

page_start('AI Content & SEO Generator', true);
?>
<link rel="stylesheet" href="/assets/admin-ai-generator.css?v=1">

<div class="ai-generator-wrap">
  <div class="ai-header">
    <div class="ai-icon">✨</div>
    <div class="ai-title">
      <h2>AI Content & SEO Generator</h2>
      <p>Automatically generate SEO-optimized descriptions, meta tags, and content for listings using AI.</p>
    </div>
  </div>

  <div class="ai-grid">
    <div class="ai-panel">
      <h3>Select Target Module</h3>
      <div class="ai-options">
        <label class="ai-radio">
          <input type="radio" name="module" value="businesses" checked>
          <span>Business Directory</span>
        </label>
        <label class="ai-radio">
          <input type="radio" name="module" value="doctors">
          <span>Doctor Profiles</span>
        </label>
        <label class="ai-radio">
          <input type="radio" name="module" value="products">
          <span>Shop Products</span>
        </label>
        <label class="ai-radio">
          <input type="radio" name="module" value="classifieds">
          <span>Classified Ads</span>
        </label>
      </div>

      <h3 style="margin-top:25px;">Input Basic Details</h3>
      <div class="ai-form-group">
        <label>Title / Name</label>
        <input type="text" id="aiName" placeholder="e.g. Al-Shifa Clinic" class="ai-input">
      </div>
      <div class="ai-form-group">
        <label>Key Features / Keywords</label>
        <input type="text" id="aiKeywords" placeholder="e.g. 24/7, experienced staff, affordable" class="ai-input">
      </div>
      <div class="ai-form-group">
        <label>Tone of Voice</label>
        <select id="aiTone" class="ai-input">
          <option value="Professional">Professional & Formal</option>
          <option value="Friendly">Friendly & Engaging</option>
          <option value="Persuasive">Persuasive (Sales-focused)</option>
        </select>
      </div>
      
      <button class="ai-btn" id="generateBtn">
        <span class="btn-text">Generate Content</span>
        <span class="btn-loader" style="display:none;">⏳</span>
      </button>
    </div>

    <div class="ai-panel ai-results">
      <h3>Generated Output</h3>
      
      <div class="ai-output-group">
        <div class="ai-label-flex">
          <label>SEO Title (Meta)</label>
          <button class="ai-copy-btn" data-target="outTitle">Copy</button>
        </div>
        <textarea id="outTitle" class="ai-textarea" rows="2" readonly placeholder="Output will appear here..."></textarea>
      </div>

      <div class="ai-output-group">
        <div class="ai-label-flex">
          <label>SEO Description (Meta)</label>
          <button class="ai-copy-btn" data-target="outDesc">Copy</button>
        </div>
        <textarea id="outDesc" class="ai-textarea" rows="3" readonly placeholder="Output will appear here..."></textarea>
      </div>

      <div class="ai-output-group">
        <div class="ai-label-flex">
          <label>Full Content Body</label>
          <button class="ai-copy-btn" data-target="outBody">Copy</button>
        </div>
        <textarea id="outBody" class="ai-textarea" rows="8" readonly placeholder="Output will appear here..."></textarea>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('generateBtn').addEventListener('click', function() {
  const btn = this;
  btn.querySelector('.btn-text').style.display = 'none';
  btn.querySelector('.btn-loader').style.display = 'inline-block';
  btn.disabled = true;

  const name = document.getElementById('aiName').value || 'Test Item';
  const tone = document.getElementById('aiTone').value;
  const keywords = document.getElementById('aiKeywords').value || '';
  const module = document.querySelector('input[name="module"]:checked').value;

  const formData = new FormData();
  formData.append('action', 'generate_content');
  formData.append('name', name);
  formData.append('tone', tone);
  formData.append('keywords', keywords);
  formData.append('module', module);
  
  fetch('ai-content-generator.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
        document.getElementById('outTitle').value = data.data.title || '';
        document.getElementById('outDesc').value = data.data.description || '';
        document.getElementById('outBody').value = data.data.body || '';
    } else {
        alert("AI Generation Error: " + (data.error || "Unknown error"));
    }
  })
  .catch(err => {
    alert("Connection Error. Check your AI provider settings.");
  })
  .finally(() => {
    btn.querySelector('.btn-text').style.display = 'inline-block';
    btn.querySelector('.btn-loader').style.display = 'none';
    btn.disabled = false;
  });
});

document.querySelectorAll('.ai-copy-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const target = document.getElementById(this.getAttribute('data-target'));
    target.select();
    document.execCommand('copy');
    this.innerText = 'Copied!';
    setTimeout(() => this.innerText = 'Copy', 2000);
  });
});
</script>

<?php require __DIR__.'/../app/end.php'; ?>
