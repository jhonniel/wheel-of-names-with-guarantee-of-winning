<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Participants</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 24px; max-width: 900px; margin: 0 auto; }
        h1 { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f6f6f6; text-align: left; }
        form.inline { display: inline; }
        .color { display: inline-block; width: 18px; height: 18px; border: 1px solid #ccc; vertical-align: middle; margin-right: 6px; }
        .row { display: flex; gap: 12px; align-items: end; flex-wrap: wrap; }
        .row > div { display: grid; }
        input[type=text] { padding: 6px 8px; }
        button { padding: 6px 10px; font-weight: 600; }
        .status { color: #067; margin-bottom: 8px; }
        .nav { margin-bottom: 16px; }
        .nav a { margin-right: 12px; }
        .bulk-actions { margin: 16px 0; padding: 12px; background: #f8f9fa; border-radius: 6px; }
        .bulk-actions button { margin-right: 8px; }
        .selected-count { margin-left: 8px; color: #666; }
        input[type="checkbox"] { margin-right: 6px; }
    </style>
    @csrf
    @routes
    @php $methodSpoof = method_field('PUT'); @endphp
</head>
<body>
    <div class="nav">
        <a href="/">Wheel</a>
        <a href="/participants">Participants</a>
    </div>
    <h1>Participants</h1>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    <form method="post" action="/participants" style="margin-top:8px;">
        @csrf
        <div class="row">
            <div>
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>Color (hex)</label>
                <input type="text" name="color" placeholder="#ff8800">
            </div>
            <div>
                <label>Active</label>
                <input type="checkbox" name="active" checked>
            </div>
            <div>
                <button type="submit">Add</button>
            </div>
        </div>
    </form>

    <form method="post" action="/participants/batch" style="margin-top:16px;">
        @csrf
        <div>
            <label>Batch Import (one name per line)</label>
            <textarea name="names" rows="4" style="width:100%; padding:8px;" placeholder="John Doe&#10;Jane Smith&#10;Bob Johnson"></textarea>
        </div>
        <button type="submit" style="margin-top:8px;">Import Names</button>
    </form>

    <!-- Wheel Logo Upload Section -->
    <div style="margin-top: 24px; padding: 16px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
        <h3 style="margin-top: 0; margin-bottom: 16px;">Wheel Logo</h3>

        <form method="post" action="/participants/logo/upload" enctype="multipart/form-data" style="margin-bottom: 16px;">
            @csrf
            <div class="row">
                <div>
                    <label>Upload Logo Image</label>
                    <input type="file" name="logo_image" accept="image/*" required style="padding: 4px;">
                </div>
                <div>
                    <label>Logo Name (optional)</label>
                    <input type="text" name="logo_name" placeholder="Custom Logo" style="padding: 6px 8px;">
                </div>
                <div>
                    <button type="submit" style="background: #007bff; color: white;">Upload & Set Active</button>
                </div>
            </div>
        </form>

        @if($logos->count() > 0)
            <div>
                <h4 style="margin-bottom: 12px;">Available Logos</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                    @foreach($logos as $logo)
                        <div style="border: 1px solid #ddd; border-radius: 6px; padding: 8px; background: white;">
                            <div style="margin-bottom: 8px;">
                                <img src="{{ asset('storage/' . $logo->image_path) }}"
                                     alt="{{ $logo->name }}"
                                     style="width: 100%; height: 100px; object-fit: contain; border-radius: 4px;">
                            </div>
                            <div style="font-weight: 600; margin-bottom: 4px;">{{ $logo->name }}</div>
                            <div style="font-size: 12px; color: #666; margin-bottom: 8px;">
                                @if($logo->is_active)
                                    <span style="color: #28a745; font-weight: 600;">● Active</span>
                                @else
                                    <span style="color: #6c757d;">Inactive</span>
                                @endif
                            </div>
                            <div style="display: flex; gap: 4px;">
                                @if(!$logo->is_active)
                                    <form method="post" action="/participants/logo/{{ $logo->id }}/activate" style="display: inline;">
                                        @csrf
                                        <button type="submit" style="padding: 4px 8px; font-size: 12px; background: #28a745; color: white; border: none; border-radius: 4px;">Set Active</button>
                                    </form>
                                @endif
                                <form method="post" action="/participants/logo/{{ $logo->id }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this logo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="padding: 4px 8px; font-size: 12px; background: #dc3545; color: white; border: none; border-radius: 4px;">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Wheel Background Upload Section -->
    <div style="margin-top: 24px; padding: 16px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
        <h3 style="margin-top: 0; margin-bottom: 16px;">Wheel Background</h3>

        <form method="post" action="/participants/background/upload" enctype="multipart/form-data" style="margin-bottom: 16px;">
            @csrf
            <div class="row">
                <div>
                    <label>Upload Background Image</label>
                    <input type="file" name="background_image" accept="image/*" required style="padding: 4px;">
                </div>
                <div>
                    <label>Background Name (optional)</label>
                    <input type="text" name="background_name" placeholder="Custom Background" style="padding: 6px 8px;">
                </div>
                <div>
                    <button type="submit" style="background: #007bff; color: white;">Upload & Set Active</button>
                </div>
            </div>
        </form>

        @if($backgrounds->count() > 0)
            <div>
                <h4 style="margin-bottom: 12px;">Available Backgrounds</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                    @foreach($backgrounds as $background)
                        <div style="border: 1px solid #ddd; border-radius: 6px; padding: 8px; background: white;">
                            <div style="margin-bottom: 8px;">
                                <img src="{{ asset('storage/' . $background->image_path) }}"
                                     alt="{{ $background->name }}"
                                     style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px;">
                            </div>
                            <div style="font-weight: 600; margin-bottom: 4px;">{{ $background->name }}</div>
                            <div style="font-size: 12px; color: #666; margin-bottom: 8px;">
                                @if($background->is_active)
                                    <span style="color: #28a745; font-weight: 600;">● Active</span>
                                @else
                                    <span style="color: #6c757d;">Inactive</span>
                                @endif
                            </div>
                            <div style="display: flex; gap: 4px;">
                                @if(!$background->is_active)
                                    <form method="post" action="/participants/background/{{ $background->id }}/activate" style="display: inline;">
                                        @csrf
                                        <button type="submit" style="padding: 4px 8px; font-size: 12px; background: #28a745; color: white; border: none; border-radius: 4px;">Set Active</button>
                                    </form>
                                @endif
                                <form method="post" action="/participants/background/{{ $background->id }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this background?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="padding: 4px 8px; font-size: 12px; background: #dc3545; color: white; border: none; border-radius: 4px;">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Wheel Settings Section -->
    <div style="margin-top: 24px; padding: 16px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
        <h3 style="margin-top: 0; margin-bottom: 16px;">Wheel Settings</h3>

        <form method="post" action="/participants/spin-duration" style="margin-bottom: 16px;">
            @csrf
            <div class="row">
                <div>
                    <label>Spin Duration (seconds)</label>
                    <input type="number" name="spin_duration" value="{{ $spinDuration }}" min="0.3" max="20" step="0.1" required style="padding: 6px 8px; width: 120px;">
                </div>
                <div>
                    <button type="submit" style="background: #28a745; color: white;">Update Duration</button>
                </div>
            </div>
            <div style="font-size: 12px; color: #666; margin-top: 4px;">
                Set how many seconds the wheel should spin before stopping (0.3-20 seconds)
            </div>
        </form>
    </div>

    <!-- Display Mode Setting -->
    <div style="margin-top: 24px; padding: 16px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
        <h3 style="margin-top: 0; margin-bottom: 16px;">Display Mode</h3>
        <p style="margin-bottom: 16px; color: #666;">Choose what to display on the main page for picking names:</p>
        <form method="post" action="/participants/display-mode" style="margin-bottom: 16px;">
            @csrf
            <div class="row">
                <div>
                    <label>Display Mode</label>
                    <select name="display_mode" style="padding: 6px 8px;">
                        <option value="both" {{ $displayMode === 'both' ? 'selected' : '' }}>Both Wheel & Rolling Names</option>
                        <option value="wheel" {{ $displayMode === 'wheel' ? 'selected' : '' }}>Wheel Only</option>
                        <option value="rolling" {{ $displayMode === 'rolling' ? 'selected' : '' }}>Rolling Names Only</option>
                    </select>
                </div>
                <div>
                    <button type="submit" style="background: #28a745; color: white;">Update Display Mode</button>
                </div>
            </div>
            <div style="font-size: 12px; color: #666; margin-top: 4px;">
                <strong>Options:</strong><br>
                • <strong>Both:</strong> Shows the spinning wheel with rolling names above it<br>
                • <strong>Wheel Only:</strong> Shows only the spinning wheel without rolling names<br>
                • <strong>Rolling Names Only:</strong> Shows only the rolling names without the wheel
            </div>
        </form>
    </div>

    <div class="bulk-actions">
        <button onclick="selectAll()">Select All</button>
        <button onclick="deselectAll()">Deselect All</button>
        <button onclick="deleteSelected()" style="background: #dc3545; color: white;">Delete Selected</button>
        <button onclick="activateSelected()" style="background: #28a745; color: white;">Activate Selected</button>
        <button onclick="deactivateSelected()" style="background: #ffc107; color: black;">Deactivate Selected</button>
        <button onclick="setGuaranteeWin()" style="background: #6f42c1; color: white;">Set Guarantee Win</button>
        <button onclick="removeGuaranteeWin()" style="background: #6c757d; color: white;">Remove Guarantee Win</button>
        <button onclick="setWeight()" style="background: #fd7e14; color: white;">Set Weight</button>
        <button onclick="resetWeight()" style="background: #20c997; color: white;">Reset Weight</button>
        <span class="selected-count" id="selectedCount">0 selected</span>
    </div>

    <table>
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()"></th>
                <th>ID</th>
                <th>Name</th>
                <th>Color</th>
                <th>Active</th>
                <th>Bias/Guarantee</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($participants as $p)
                <tr>
                    <td><input type="checkbox" class="participant-checkbox" value="{{ $p->id }}" onchange="updateSelectedCount()"></td>
                    <td>{{ $p->id }}</td>
                    <td>
                        <form class="inline" method="post" action="/participants/{{ $p->id }}">
                            @csrf
                            {!! $methodSpoof !!}
                            <input type="text" name="name" value="{{ $p->name }}" required>
                            <input type="text" name="color" value="{{ $p->color }}" placeholder="#rrggbb" style="width:110px;">
                            <label style="margin-left:6px;">
                                <input type="checkbox" name="active" {{ $p->active ? 'checked' : '' }}> Active
                            </label>
                            <button type="submit">Save</button>
                        </form>
                    </td>
                    <td>
                        <span class="color" style="background: {{ $p->color }}"></span>
                        <code>{{ $p->color }}</code>
                    </td>
                    <td>{{ $p->active ? 'Yes' : 'No' }}</td>
                    <td>
                        <form class="inline" method="post" action="/participants/{{ $p->id }}/config">
                            @csrf
                            <label>Weight
                                <input type="text" name="weight" value="{{ optional($p->config)->weight ?? 1 }}" style="width:70px;">
                            </label>
                            <label style="margin-left:6px;">Guarantee
                                <input type="text" name="guarantee_quota" value="{{ optional($p->config)->guarantee_quota ?? 0 }}" style="width:70px;">
                            </label>
                            <button type="submit">Save</button>
                        </form>
                    </td>
                    <td>
                        @if ($p->active)
                        <form class="inline" method="post" action="/participants/{{ $p->id }}/deactivate">
                            @csrf
                            <button type="submit">Deactivate</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

<script>
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.participant-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count + ' selected';

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.participant-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    if (count === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (count === allCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.participant-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });

    updateSelectedCount();
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.participant-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = true);
    updateSelectedCount();
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.participant-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    updateSelectedCount();
}

function getSelectedIds() {
    const checkboxes = document.querySelectorAll('.participant-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function deleteSelected() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Please select participants to delete.');
        return;
    }

    if (confirm(`Are you sure you want to delete ${selectedIds.length} participant(s)?`)) {
        // Create a form to submit the bulk delete
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/participants/bulk-delete';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }
}

function activateSelected() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Please select participants to activate.');
        return;
    }

    bulkAction(selectedIds, 'activate');
}

function deactivateSelected() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Please select participants to deactivate.');
        return;
    }

    bulkAction(selectedIds, 'deactivate');
}

function setGuaranteeWin() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Please select participants to set guarantee win.');
        return;
    }

    const guaranteeQuota = prompt('Enter guarantee quota (number of guaranteed wins):', '1');
    if (guaranteeQuota === null) return; // User cancelled

    const quota = parseInt(guaranteeQuota);
    if (isNaN(quota) || quota < 0) {
        alert('Please enter a valid number (0 or greater).');
        return;
    }

    bulkGuaranteeAction(selectedIds, quota);
}

function removeGuaranteeWin() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Please select participants to remove guarantee win.');
        return;
    }

    if (confirm(`Remove guarantee win for ${selectedIds.length} participant(s)?`)) {
        bulkGuaranteeAction(selectedIds, 0);
    }
}

function setWeight() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Please select participants to set weight.');
        return;
    }

    const weight = prompt('Enter weight (higher = better chance to win, 1 = normal):', '1');
    if (weight === null) return; // User cancelled

    const weightValue = parseFloat(weight);
    if (isNaN(weightValue) || weightValue < 0) {
        alert('Please enter a valid number (0 or greater).');
        return;
    }

    bulkWeightAction(selectedIds, weightValue);
}

function resetWeight() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Please select participants to reset weight.');
        return;
    }

    if (confirm(`Reset weight to 1 (normal) for ${selectedIds.length} participant(s)?`)) {
        bulkWeightAction(selectedIds, 1);
    }
}

function bulkWeightAction(ids, weight) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/participants/bulk-weight';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const weightInput = document.createElement('input');
    weightInput.type = 'hidden';
    weightInput.name = 'weight';
    weightInput.value = weight;
    form.appendChild(weightInput);

    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

function bulkGuaranteeAction(ids, guaranteeQuota) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/participants/bulk-guarantee';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const quotaInput = document.createElement('input');
    quotaInput.type = 'hidden';
    quotaInput.name = 'guarantee_quota';
    quotaInput.value = guaranteeQuota;
    form.appendChild(quotaInput);

    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

function bulkAction(ids, action) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/participants/bulk-${action}`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>
</body>
</html>


