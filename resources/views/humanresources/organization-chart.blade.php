@extends('layout.mainlayout')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="hrd-approval-inbox">Approval</a></li>
        <li class="breadcrumb-item active"><span class="active">Bagan Struktur Organisasi</span></li>
    </ol>
</div>

<div class="card mt-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="small text-muted mb-1">Direktorat</label>
                <select id="OrgFilterDirectorat" class="form-control form-control-sm">
                    <option value="">Semua direktorat</option>
                    @foreach($directorats as $dir)
                    <option value="{{ $dir }}">{{ $dir }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small text-muted mb-1">Departemen</label>
                <select id="OrgFilterDepartment" class="form-control form-control-sm">
                    <option value="">Semua departemen</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small text-muted mb-1">Cari Posisi / Karyawan</label>
                <div class="input-group input-group-sm position-relative">
                    <input type="text" id="OrgSearchQuery" class="form-control" placeholder="Jabatan, nama, NIK..." autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="OrgChartSearch();">Cari</button>
                    </div>
                    <div id="OrgSearchDropdown" class="dropdown-menu w-100 shadow-sm" style="display: none; max-height: 220px; overflow-y: auto; position: absolute; top: 100%; left: 0; z-index: 1050;"></div>
                </div>
            </div>
            <div class="col-md-3">
                <label class="small text-muted mb-1">Orientasi bagan</label>
                <select id="OrgOrientation" class="form-control form-control-sm">
                    <option value="h" selected>Horizontal (kiri ke kanan)</option>
                    <option value="v">Vertikal (atas ke bawah)</option>
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="OrgFilterSystem" />
                    <label class="form-check-label small" for="OrgFilterSystem">Tampilkan akun sistem / dummy</label>
                </div>
            </div>
        </div>

        <hr />

        <button type="button" class="btn btn-primary btn-sm" onclick="OrgChartLoad();">Tampilkan</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="OrgChartFit();">Fit ke layar</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="OrgChartResetView();">Reset Zoom</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="OrgChartZoom(1.25);">Zoom +</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="OrgChartZoom(0.8);">Zoom -</button>
        <span class="mx-2 text-muted">|</span>
        <button type="button" class="btn btn-success btn-sm" onclick="OrgChartExportPng();">Export PNG</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="OrgChartExportPdf();">Export PDF</button>
        <button type="button" class="btn btn-info btn-sm" onclick="OrgChartExportSvg();">Export SVG</button>
    </div>
</div>

<div id="OrgChartAlert" class="mt-3"></div>

<div class="row mt-3">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body p-0">
                <div id="OrgChartWrap" style="position:relative; width:100%; height:640px; overflow:hidden; background:#f7f9fc; border-radius:4px;">
                    <svg id="OrgChart" width="100%" height="100%"></svg>
                    
                    <!-- Minimap Navigator -->
                    <svg id="OrgMinimap" style="position:absolute; top:50px; left:50px; width:180px; height:120px; background:#ffffff; border:1px solid #d5dce6; border-radius:4px; box-shadow:0 4px 10px rgba(0,0,0,0.08); z-index:10;">
                        <g id="OrgMinimapViewport"></g>
                        <rect id="OrgMinimapLens" fill="rgba(31, 111, 235, 0.2)" stroke="#1f6feb" stroke-width="1.5" rx="2" style="cursor: pointer; pointer-events: none;"></rect>
                    </svg>
                </div>
            </div>
            <div class="card-footer small text-muted">
                <span id="OrgChartStats">-</span>
                <span class="float-right">
                    @if($canManage)
                    Tarik ikon <b>:::</b> untuk <b>Drag & Drop</b> atasan posisi. <b>Scroll</b> zoom, <b>Drag</b> geser.
                    @else
                    Hanya admin yang dapat mengubah atasan posisi. <b>Scroll</b> zoom, <b>Drag</b> geser.
                    @endif
                </span>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header"><strong>Detail Posisi</strong></div>
            <div class="card-body" id="OrgChartPanel">
                <p class="text-muted small mb-0">Klik salah satu kotak pada bagan.</p>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header"><strong>Kualitas Data</strong></div>
            <div class="card-body" id="OrgChartIssues">
                <p class="text-muted small mb-0">-</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/chart-nvd3/js/d3.min.js') }}"></script>
<script>
var ORG_CHART_URL      = "{{ url('hrd-organization-chart-data') }}";
var ORG_CHART_SAVE_URL = "{{ url('hrd-organization-chart-save-parent') }}";
var ORG_CSRF           = "{{ csrf_token() }}";
var ORG_CAN_MANAGE     = {{ $canManage ? 'true' : 'false' }};

var OrgChartState = {
    nodes: {},
    roots: [],
    selected: null,
    orientation: 'h',
    zoom: null,
    ready: false,
    collapsed: {}
};

var ORG_NODE_W = 210;
var ORG_NODE_H = 74;

function orgEsc(s) {
    return String(s === null || s === undefined ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function orgTruncate(s, max) {
    s = String(s === null || s === undefined ? '' : s);
    return s.length > max ? s.substring(0, max - 1) + '\u2026' : s;
}

function orgDeptColor(dept) {
    var palette = ['#1f6feb', '#0f9d58', '#e8710a', '#8e44ad', '#c0392b', '#0b7285', '#5c6bc0', '#2e7d32', '#ad1457', '#455a64'];
    var sum = 0;
    dept = String(dept || '');
    for (var i = 0; i < dept.length; i++) { sum += dept.charCodeAt(i); }
    return palette[sum % palette.length];
}

function orgBlock(msg) {
    if ($.fn.block) { $('#OrgChartWrap').block({ message: '<div class="small">' + orgEsc(msg) + '</div>' }); }
}

function orgUnblock() {
    if ($.fn.unblock) { $('#OrgChartWrap').unblock(); }
}

function OrgChartLoad() {
    var params = [];
    var dir = $('#OrgFilterDirectorat').val();
    var dept = $('#OrgFilterDepartment').val();
    var sys = $('#OrgFilterSystem').is(':checked') ? 1 : 0;

    if (dir) { params.push('directorat=' + encodeURIComponent(dir)); }
    if (dept) { params.push('department=' + encodeURIComponent(dept)); }
    params.push('include_system=' + sys);

    orgBlock('Memuat struktur posisi...');

    $.ajax({
        url: ORG_CHART_URL + '?' + params.join('&'),
        type: 'GET',
        dataType: 'json',
        cache: false
    }).done(function (res) {
        orgUnblock();
        if (!res || res.status !== 'success') {
            alert('Gagal memuat data bagan.');
            return;
        }

        OrgChartState.nodes = {};
        for (var i = 0; i < res.nodes.length; i++) {
            var n = res.nodes[i];
            n.children = [];
            OrgChartState.nodes[n.id] = n;
        }

        for (var id in OrgChartState.nodes) {
            var node = OrgChartState.nodes[id];
            if (node.parent_id && OrgChartState.nodes[node.parent_id]) {
                OrgChartState.nodes[node.parent_id].children.push(node.id);
            }
        }

        OrgChartState.roots = res.roots || [];
        if (OrgChartState.roots.length === 0) {
            for (var id in OrgChartState.nodes) {
                var node = OrgChartState.nodes[id];
                if (!node.parent_id || !OrgChartState.nodes[node.parent_id]) {
                    OrgChartState.roots.push(node.id);
                }
            }
        }

        OrgChartState.selected = null;
        OrgChartRender();
        OrgChartIssues(res.issues || []);
        OrgChartResetView();
    }).fail(function (xhr) {
        orgUnblock();
        alert('Gagal memuat data bagan (HTTP ' + xhr.status + ').');
    });
}

function OrgChartRender() {
    var svg = d3.select('#OrgChart');
    var currentScale = OrgChartState.zoom ? OrgChartState.zoom.scale() : null;
    var currentTranslate = OrgChartState.zoom ? OrgChartState.zoom.translate() : null;

    svg.selectAll('*').remove();

    var nodeCount = Object.keys(OrgChartState.nodes).length;
    if (nodeCount === 0) {
        svg.append('text').attr('x', 30).attr('y', 40).attr('fill', '#8a94a6').text('Tidak ada data struktur organisasi.');
        $('#OrgChartStats').text('-');
        return;
    }

    OrgChartState.orientation = $('#OrgOrientation').val() || 'h';
    var horizontal = OrgChartState.orientation === 'h';

    var virtualRoot = { id: '__root__', children: [] };
    for (var r = 0; r < OrgChartState.roots.length; r++) {
        var rootNode = OrgChartState.nodes[OrgChartState.roots[r]];
        if (rootNode) { virtualRoot.children.push(orgBuildTree(rootNode.id, {})); }
    }

    var layout = d3.layout.tree();
    if (horizontal) {
        layout.nodeSize([ORG_NODE_H + 14, ORG_NODE_W + 78]);
    } else {
        layout.nodeSize([ORG_NODE_W + 14, ORG_NODE_H + 82]);
    }
    var laidOut = layout.nodes(virtualRoot);
    var links = layout.links(laidOut);

    var minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    var positioned = [];
    for (var i = 0; i < laidOut.length; i++) {
        var d = laidOut[i];
        var px, py;
        if (horizontal) { px = d.y; py = d.x; } else { px = d.x; py = d.y; }
        d.px = px; d.py = py;
        if (d.id !== '__root__') {
            positioned.push(d);
            if (px < minX) minX = px;
            if (py < minY) minY = py;
            if (px > maxX) maxX = px;
            if (py > maxY) maxY = py;
        }
    }

    var pad = 60;
    var offsetX = -minX + pad;
    var offsetY = -minY + pad;
    OrgChartState.offsetX = offsetX;
    OrgChartState.offsetY = offsetY;

    var contentW = (maxX - minX) + ORG_NODE_W + pad * 2;
    var contentH = (maxY - minY) + ORG_NODE_H + pad * 2;
    if (!isFinite(contentW) || contentW <= 0) { contentW = 900; }
    if (!isFinite(contentH) || contentH <= 0) { contentH = 600; }

    var viewport = svg.append('g').attr('id', 'OrgChartViewport');

    viewport.append('rect')
        .attr('x', -pad).attr('y', -pad)
        .attr('width', contentW + pad * 2).attr('height', contentH + pad * 2)
        .attr('fill', '#f7f9fc');

    var linkLayer = viewport.append('g').attr('class', 'org-links');
    var nodeLayer = viewport.append('g').attr('class', 'org-nodes');

    for (var l = 0; l < links.length; l++) {
        var src = links[l].source;
        var tgt = links[l].target;
        if (src.id === '__root__' || tgt.id === '__root__') { continue; }

        var x1 = src.px + offsetX, y1 = src.py + offsetY;
        var x2 = tgt.px + offsetX, y2 = tgt.py + offsetY;
        var path;

        if (horizontal) {
            var mx = (x1 + ORG_NODE_W + x2) / 2;
            y1 = y1 + ORG_NODE_H / 2;
            y2 = y2 + ORG_NODE_H / 2;
            path = 'M' + (x1 + ORG_NODE_W) + ',' + y1 + ' H' + mx + ' V' + y2 + ' H' + x2;
        } else {
            var my = (y1 + ORG_NODE_H + y2) / 2;
            x1 = x1 + ORG_NODE_W / 2;
            x2 = x2 + ORG_NODE_W / 2;
            path = 'M' + x1 + ',' + (y1 + ORG_NODE_H) + ' V' + my + ' H' + x2 + ' V' + y2;
        }

        linkLayer.append('path')
            .attr('d', path)
            .attr('fill', 'none')
            .attr('stroke', '#b9c3d1')
            .attr('stroke-width', 1.4);
    }

    /* --- LOOP KOTAK NODE & DRAG DROP --- */
    for (var k = 0; k < positioned.length; k++) {
        var nd = positioned[k];
        var data = OrgChartState.nodes[nd.id];
        if (!data) { continue; }

        data.px = nd.px;
        data.py = nd.py;

        let currentId = data.id;
        let nodeData = data; // TAMPUNG SECARA LOKAL KETAT

        var g = nodeLayer.append('g')
            .attr('class', 'org-node')
            .attr('id', 'node-' + nodeData.id)
            .attr('transform', 'translate(' + (nd.px + offsetX) + ',' + (nd.py + offsetY) + ')');

        var accent = nodeData.is_vacant ? '#e74c3c' : orgDeptColor(nodeData.department);
        var selected = OrgChartState.selected === nodeData.id;

        // Background Kotak Utama
        var nodeRect = g.append('rect')
            .attr('class', 'node-rect')
            .attr('width', ORG_NODE_W)
            .attr('height', ORG_NODE_H)
            .attr('rx', 6).attr('ry', 6)
            .attr('fill', nodeData.is_vacant ? '#fdf2f2' : '#ffffff')
            .attr('stroke', selected ? '#1f6feb' : (nodeData.is_vacant ? '#e74c3c' : '#d5dce6'))
            .attr('stroke-width', selected ? 2.4 : 1)
            .style('cursor', 'pointer')
            .on('click', function () {
                d3.event.stopPropagation();
                OrgChartSelect(currentId); // Aman menggunakan variabel lokal currentId
            });

        // Garis Aksen Kiri
        g.append('rect')
            .attr('width', 5).attr('height', ORG_NODE_H)
            .attr('rx', 2).attr('ry', 2)
            .attr('fill', accent);

        // Teks Jabatan, Nama, Dept
        g.append('text')
            .attr('x', 14).attr('y', 22)
            .attr('font-size', 12)
            .attr('font-weight', '700')
            .attr('fill', nodeData.is_vacant ? '#c0392b' : '#16324f')
            .style('pointer-events', 'none')
            .text(orgTruncate(nodeData.position, 25));

        g.append('text')
            .attr('x', 14).attr('y', 40)
            .attr('font-size', 11)
            .attr('fill', '#54657e')
            .style('pointer-events', 'none')
            .text(orgTruncate(nodeData.name, 28));

        g.append('text')
            .attr('x', 14).attr('y', 57)
            .attr('font-size', 10)
            .attr('fill', accent)
            .attr('font-weight', '600')
            .style('pointer-events', 'none')
            .text(orgTruncate(nodeData.department + ' \u00B7 L' + nodeData.level, 28));

        // --- HANDLE DRAG & DROP KHUSUS (IKON PEGANGAN :::) ---
        var dragHandle = g.append('g')
            .attr('class', 'drag-handle')
            .attr('transform', 'translate(' + (ORG_NODE_W - 24) + ', 6)')
            .style('cursor', 'grab');

        dragHandle.append('rect')
            .attr('width', 18).attr('height', 18)
            .attr('fill', '#eef2f7')
            .attr('stroke', '#cbd5e1')
            .attr('rx', 3);

        dragHandle.append('text')
            .attr('x', 9).attr('y', 12)
            .attr('text-anchor', 'middle')
            .attr('font-size', 10)
            .attr('font-weight', 'bold')
            .attr('fill', '#64748b')
            .text(':::');

        // Mengatur perilaku Drag & Drop D3 dengan Presisi Mutlak 1:1 Memperhitungkan Zoom & Pan
        (function (sourceNode, groupElem) {
            var xOffset = 0, yOffset = 0;
            var lastTargetId = null;

            var dragBehavior = d3.behavior.drag()
                .on('dragstart', function () {
                    d3.event.sourceEvent.stopPropagation();
                    groupElem.style('opacity', 0.4);

                    // Dapatkan skala dan translasi zoom saat ini
                    var scale = OrgChartState.zoom ? OrgChartState.zoom.scale() : 1;
                    var translate = OrgChartState.zoom ? OrgChartState.zoom.translate() : [0, 0];

                    // Konversi koordinat kursor layar ke dalam sistem koordinat internal SVG (memperhitungkan zoom & pan)
                    var mouse = d3.mouse(svg.node());
                    var svgX = (mouse[0] - translate[0]) / scale;
                    var svgY = (mouse[1] - translate[1]) / scale;

                    // Posisi render node saat ini di dalam kanvas
                    var currentTransformX = sourceNode.px + offsetX;
                    var currentTransformY = sourceNode.py + offsetY;

                    // Hitung offset akurat dari titik klik kursor ke posisi sudut node
                    xOffset = currentTransformX - svgX;
                    yOffset = currentTransformY - svgY;
                })
                .on('drag', function () {
                    var scale = OrgChartState.zoom ? OrgChartState.zoom.scale() : 1;
                    var translate = OrgChartState.zoom ? OrgChartState.zoom.translate() : [0, 0];

                    var mouse = d3.mouse(svg.node());
                    var svgX = (mouse[0] - translate[0]) / scale;
                    var svgY = (mouse[1] - translate[1]) / scale;

                    // Terapkan posisi baru dengan mempertahankan offset awal secara presisi 1:1
                    var currentX = svgX + xOffset;
                    var currentY = svgY + yOffset;

                    groupElem.attr('transform', 'translate(' + currentX + ',' + currentY + ')');

                    sourceNode.currentDragX = currentX;
                    sourceNode.currentDragY = currentY;

                    // Deteksi node target di bawah kursor secara real-time untuk highlight hijau
                    var currentTargetId = null;
                    for (var id in OrgChartState.nodes) {
                        var other = OrgChartState.nodes[id];
                        if (other.id === sourceNode.id) continue;

                        var tx = other.px + OrgChartState.offsetX;
                        var ty = other.py + OrgChartState.offsetY;

                        if (currentX >= tx - 20 && currentX <= tx + ORG_NODE_W + 20 &&
                            currentY >= ty - 20 && currentY <= ty + ORG_NODE_H + 20) {
                            currentTargetId = other.id;
                            break;
                        }
                    }

                    if (lastTargetId !== currentTargetId) {
                        if (lastTargetId) {
                            d3.select('#node-' + lastTargetId + ' .node-rect')
                                .attr('stroke', '#d5dce6').attr('stroke-width', 1);
                        }
                        if (currentTargetId) {
                            d3.select('#node-' + currentTargetId + ' .node-rect')
                                .attr('stroke', '#28a745').attr('stroke-width', 3);
                        }
                        lastTargetId = currentTargetId;
                    }
                })
                .on('dragend', function () {
                    groupElem.style('opacity', 1);

                    if (lastTargetId) {
                        d3.select('#node-' + lastTargetId + ' .node-rect')
                            .attr('stroke', '#d5dce6').attr('stroke-width', 1);
                    }

                    var dropX = sourceNode.currentDragX !== undefined ? sourceNode.currentDragX : (sourceNode.px + offsetX);
                    var dropY = sourceNode.currentDragY !== undefined ? sourceNode.currentDragY : (sourceNode.py + offsetY);
                    var targetNodeId = null;

                    for (var id in OrgChartState.nodes) {
                        var other = OrgChartState.nodes[id];
                        if (other.id === sourceNode.id) continue;

                        var tx = other.px + OrgChartState.offsetX;
                        var ty = other.py + OrgChartState.offsetY;

                        if (dropX >= tx - 20 && dropX <= tx + ORG_NODE_W + 20 &&
                            dropY >= ty - 20 && dropY <= ty + ORG_NODE_H + 20) {
                            targetNodeId = other.id;
                            break;
                        }
                    }

                    if (targetNodeId) {
                        var targetNode = OrgChartState.nodes[targetNodeId];
                        var draggedTitle = sourceNode.position + ' (' + sourceNode.name + ')';
                        var targetTitle = targetNode.position + ' (' + targetNode.name + ')';

                        if (confirm('Jadikan posisi "' + draggedTitle + '" di bawah atasan baru "' + targetTitle + '"?')) {
                            OrgChartSaveParentDragDrop(sourceNode.id, targetNodeId);
                            return;
                        }
                    }

                    // --- ANIMASI TRANSISI MULUS DENGAN ATTR-TWEEN (D3 v3) ---
                    var originalX = sourceNode.px + offsetX;
                    var originalY = sourceNode.py + offsetY;
                    
                    var startTransform = groupElem.attr('transform');
                    var endTransform = 'translate(' + originalX + ',' + originalY + ')';

                    groupElem.transition()
                        .duration(450) // Durasi animasi dalam milidetik (0.45 detik)
                        .ease('cubic-out')
                        .attrTween('transform', function() {
                            return d3.interpolateString(startTransform, endTransform);
                        })
                        .each('end', function() {
                            OrgChartRender(); // Render ulang setelah animasi selesai sempurna
                        });
                });

            if (ORG_CAN_MANAGE) {
                dragHandle.call(dragBehavior);
            } else {
                dragHandle.remove();
            }
        })(nodeData, g);

        // --- TOMBOL EXPAND / COLLAPSE ---
        var hasChildren = nodeData.children && nodeData.children.length > 0;
        if (hasChildren) {
            var isCollapsed = !!OrgChartState.collapsed[nodeData.id];
            var toggleX = horizontal ? ORG_NODE_W - 15 : ORG_NODE_W / 2;
            var toggleY = horizontal ? ORG_NODE_H / 2 : ORG_NODE_H - 12;

            var toggleGroup = g.append('g')
                .attr('class', 'org-toggle')
                .attr('transform', 'translate(' + toggleX + ',' + toggleY + ')')
                .style('cursor', 'pointer')
                .on('mousedown', function () {
                    d3.event.stopPropagation();
                })
                .on('click', function () {
                    d3.event.stopPropagation();
                    OrgChartToggleCollapse(currentId);
                });

            toggleGroup.append('circle').attr('r', 10).attr('fill', '#ffffff').attr('stroke', '#1f6feb').attr('stroke-width', 1.5);
            toggleGroup.append('text').attr('text-anchor', 'middle').attr('dominant-baseline', 'central').attr('font-size', 12).attr('font-weight', 'bold').attr('fill', '#1f6feb').text(isCollapsed ? '+' : '-');
        }

        g.datum(nodeData);
    }

    svg.on('click', function () {
        OrgChartState.selected = null;
        OrgChartPanelEmpty();
        OrgChartRender();
    });

    OrgChartState.zoom = d3.behavior.zoom()
        .scaleExtent([0.15, 3])
        .on('zoom', function () {
            viewport.attr('transform', 'translate(' + d3.event.translate + ') scale(' + d3.event.scale + ')');
            OrgChartUpdateMinimapLens();
        });
    svg.call(OrgChartState.zoom).on('dblclick.zoom', null);

    OrgChartState.contentW = contentW;
    OrgChartState.contentH = contentH;
    OrgChartState.ready = true;

    $('#OrgChartStats').text(positioned.length + ' posisi \u00B7 ' + OrgChartState.roots.length + ' akar bagan');
    OrgChartRenderMinimap(contentW, contentH, positioned, links, offsetX, offsetY);

    if (currentScale !== null && currentTranslate !== null) {
        OrgChartState.zoom.scale(currentScale).translate(currentTranslate);
        viewport.attr('transform', 'translate(' + currentTranslate + ') scale(' + currentScale + ')');
    } else {
        OrgChartResetView();
    }
}

function OrgChartSaveParentDragDrop(id, parentId) {
    orgBlock('Menyimpan perubahan posisi...');
    $.ajax({
        url: ORG_CHART_SAVE_URL,
        type: 'POST',
        dataType: 'json',
        data: { _token: ORG_CSRF, ID: id, Parent_ID: parentId }
    }).done(function (res) {
        orgUnblock();
        if (res.status === 'success') {
            OrgChartLoad();
        } else {
            alert(res.message);
            OrgChartRender();
        }
    }).fail(function (xhr) {
        orgUnblock();
        alert('Gagal memperbarui atasan posisi.');
        OrgChartRender();
    });
}


/* ----------------------------------------------------------------- issues */
function OrgChartIssues(issues) {
    if (!issues || !issues.length) {
        $('#OrgChartIssues').html('<p class="text-success small mb-0">Tidak ada masalah data terdeteksi.</p>');
        return;
    }

    var counts = {};
    for (var i = 0; i < issues.length; i++) {
        counts[issues[i].type] = (counts[issues[i].type] || 0) + 1;
    }

    var labels = {
        DUPLICATE_NIK: 'NIK ganda',
        MULTI_ROOT: 'Node tanpa atasan (akar ganda)',
        NO_POSITION: 'Jabatan kosong',
        ORPHAN: 'Node terputus'
    };

    var html = '<ul class="list-unstyled small mb-2">';
    for (var t in counts) {
        html += '<li><span class="badge badge-warning">' + counts[t] + '</span> ' + orgEsc(labels[t] || t) + '</li>';
    }
    html += '</ul><ul class="small text-muted mb-0" style="padding-left:16px;max-height:180px;overflow:auto">';
    for (var j = 0; j < Math.min(issues.length, 25); j++) {
        html += '<li>' + orgEsc(issues[j].message) + '</li>';
    }
    if (issues.length > 25) {
        html += '<li>&hellip; dan ' + (issues.length - 25) + ' lainnya</li>';
    }
    html += '</ul>';
    $('#OrgChartIssues').html(html);
}

function OrgChartSearch() {
    var keyword = $('#OrgSearchQuery').val().toLowerCase().trim();
    if (!keyword) return;

    var ids = Object.keys(OrgChartState.nodes);
    for (var i = 0; i < ids.length; i++) {
        var node = OrgChartState.nodes[ids[i]];
        if ((node.position || '').toLowerCase().indexOf(keyword) !== -1 ||
            (node.name || '').toLowerCase().indexOf(keyword) !== -1 ||
            (node.nik || '').toLowerCase().indexOf(keyword) !== -1) {
            
            // Otomatis pilih dan fokus ke node pertama yang cocok
            OrgChartSelect(node.id);
            $('#OrgSearchDropdown').hide();
            return;
        }
    }
    alert('Posisi / Karyawan dengan kata kunci "' + keyword + '" tidak ditemukan.');
}

// Event listener enter key pada input pencarian
$(document).ready(function() {
    $('#OrgSearchQuery').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            OrgChartSearch();
        }
    });
});

/* ----------------------------------------------------------------- export */
function orgSerializeSvg() {
    var svgEl = document.getElementById('OrgChart');
    var vp = document.getElementById('OrgChartViewport');
    if (!vp) { return null; }

    var bb;
    try {
        bb = vp.getBBox();
    } catch (e) {
        bb = { x: 0, y: 0, width: OrgChartState.contentW || 1200, height: OrgChartState.contentH || 800 };
    }

    if (!bb || !isFinite(bb.width) || bb.width <= 0) {
        bb = { x: 0, y: 0, width: OrgChartState.contentW || 1200, height: OrgChartState.contentH || 800 };
    }

    var pad = 40;
    var w = Math.ceil(bb.width + pad * 2);
    var h = Math.ceil(bb.height + pad * 2);

    // Clone SVG bagan utama (tanpa minimap)
    var clone = svgEl.cloneNode(true);
    clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    clone.setAttribute('width', w);
    clone.setAttribute('height', h);
    clone.setAttribute('viewBox', (bb.x - pad) + ' ' + (bb.y - pad) + ' ' + w + ' ' + h);

    // Hapus efek pan/zoom transform pada clone agar gambar sejajar murni
    var cloneVp = clone.querySelector('#OrgChartViewport');
    if (cloneVp) { cloneVp.removeAttribute('transform'); }

    // Tambahkan background putih solid
    var bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    bg.setAttribute('x', bb.x - pad);
    bg.setAttribute('y', bb.y - pad);
    bg.setAttribute('width', w);
    bg.setAttribute('height', h);
    bg.setAttribute('fill', '#ffffff');
    clone.insertBefore(bg, clone.firstChild);

    return {
        xml: new XMLSerializer().serializeToString(clone),
        width: w,
        height: h
    };
}

function orgSvgToCanvas(snap, maxDim) {
    return new Promise(function (resolve, reject) {
        maxDim = maxDim || 4000; // Proteksi memori batas maksimum dimensi canvas
        var scale = 1;
        if (snap.width > maxDim || snap.height > maxDim) {
            scale = Math.min(maxDim / snap.width, maxDim / snap.height);
        }

        var img = new Image();
        img.onload = function () {
            var canvas = document.createElement('canvas');
            canvas.width = Math.ceil(snap.width * scale);
            canvas.height = Math.ceil(snap.height * scale);
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            resolve(canvas);
        };
        img.onerror = function () {
            reject(new Error('Gagal mengonversi struktur bagan ke format gambar.'));
        };
        // Menggunakan encodeURIComponent aman untuk karakter UTF-8
        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(snap.xml);
    });
}

function orgDownloadBlob(blob, filename) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function () { URL.revokeObjectURL(url); }, 1500);
}

function OrgChartExportSvg() {
    var snap = orgSerializeSvg();
    if (!snap) { alert('Bagan belum siap diexport.'); return; }
    orgDownloadBlob(new Blob([snap.xml], { type: 'image/svg+xml;charset=utf-8' }), 'bagan-struktur-organisasi.svg');
}

function OrgChartExportPng() {
    var snap = orgSerializeSvg();
    if (!snap) { alert('Bagan belum siap diexport.'); return; }

    orgBlock('Menyiapkan berkas PNG...');

    orgSvgToCanvas(snap, 5000).then(function (canvas) {
        orgUnblock();
        canvas.toBlob(function (blob) {
            orgDownloadBlob(blob, 'bagan-struktur-organisasi.png');
        }, 'image/png');
    }).catch(function (err) {
        orgUnblock();
        alert(err.message);
    });
}

function orgLoadJsPdf() {
    return new Promise(function (resolve, reject) {
        if (window.jspdf && window.jspdf.jsPDF) { resolve(window.jspdf.jsPDF); return; }

        var existing = document.getElementById('OrgJsPdfScript');
        if (existing) {
            existing.addEventListener('load', function () {
                window.jspdf ? resolve(window.jspdf.jsPDF) : reject(new Error('Pustaka jsPDF gagal dimuat.'));
            });
            existing.addEventListener('error', function () { reject(new Error('Pustaka jsPDF gagal dimuat.')); });
            return;
        }

        var s = document.createElement('script');
        s.id = 'OrgJsPdfScript';
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        s.onload = function () {
            window.jspdf ? resolve(window.jspdf.jsPDF) : reject(new Error('Pustaka jsPDF gagal dimuat.'));
        };
        s.onerror = function () { reject(new Error('Gagal mengunduh script jsPDF (periksa koneksi internet).')); };
        document.head.appendChild(s);
    });
}

function OrgChartExportPdf() {
    var snap = orgSerializeSvg();
    if (!snap) { alert('Bagan belum siap diexport.'); return; }

    orgBlock('Menyiapkan berkas PDF...');

    orgSvgToCanvas(snap, 4000).then(function (canvas) {
        return orgLoadJsPdf().then(function (JsPDF) {
            // Skala otomatis ke format standar A4 / A3 / Custom Landscape
            var isWide = canvas.width >= canvas.height;
            var pdf = new JsPDF({
                orientation: isWide ? 'landscape' : 'portrait',
                unit: 'mm',
                format: 'a3', // Menggunakan format A3 agar bagan 50+ node terlihat jernih saat di-zoom
                compress: true
            });

            var pageWidth = pdf.internal.pageSize.getWidth();
            var pageHeight = pdf.internal.pageSize.getHeight();

            // Hitung rasio penyusutan agar bagan pas berada di dalam 1 halaman PDF (Fit to Page)
            var ratio = Math.min(pageWidth / canvas.width, pageHeight / canvas.height);
            var imgW = canvas.width * ratio;
            var imgH = canvas.height * ratio;

            var x = (pageWidth - imgW) / 2;
            var y = (pageHeight - imgH) / 2;

            pdf.addImage(canvas.toDataURL('image/png'), 'PNG', x, y, imgW, imgH);
            pdf.save('bagan-struktur-organisasi.pdf');
            orgUnblock();
        });
    }).catch(function (err) {
        orgUnblock();
        alert(err.message + '\n\nAlternatif: Gunakan Export PNG lalu cetak ke PDF.');
    });
}

function OrgChartToggleCollapse(id) {
    if (OrgChartState.collapsed[id]) {
        delete OrgChartState.collapsed[id];
    } else {
        OrgChartState.collapsed[id] = true;
    }
    OrgChartRender();
}

function orgBuildTree(id, visited) {
    var node = OrgChartState.nodes[id];
    if (!node) return { id: id, name: '?', children: [] };
    if (visited[id]) return { id: id, name: node.name, children: [] };
    visited[id] = true;

    var out = {
        id: node.id,
        name: node.name,
        position: node.position,
        department: node.department,
        level: node.level
    };

    if (OrgChartState.collapsed[node.id]) {
        out.children = [];
        return out;
    }

    var kids = [];
    for (var i = 0; i < node.children.length; i++) {
        var cid = node.children[i];
        if (!OrgChartState.nodes[cid]) continue;
        if (visited[cid]) continue;
        kids.push(orgBuildTree(cid, visited));
    }
    out.children = kids;
    return out;
}

function OrgChartResetView() {
    if (!OrgChartState.zoom || !OrgChartState.contentW) return;
    var wrap = document.getElementById('OrgChartWrap');
    var w = wrap.clientWidth || 900;
    var scale = Math.min(1, w / OrgChartState.contentW);
    scale = Math.max(0.2, scale);
    var tx = (w - OrgChartState.contentW * scale) / 2;
    var ty = 10;
    OrgChartState.zoom.scale(scale).translate([tx, ty]);
    d3.select('#OrgChartViewport').attr('transform', 'translate(' + tx + ',' + ty + ') scale(' + scale + ')');
    OrgChartUpdateMinimapLens(); // FIX: Update lensa saat reset
}

function OrgChartFit() {
    if (!OrgChartState.zoom || !OrgChartState.contentW) return;
    var wrap = document.getElementById('OrgChartWrap');
    var w = wrap.clientWidth || 900;
    var h = wrap.clientHeight || 600;
    var scale = Math.min(w / OrgChartState.contentW, h / OrgChartState.contentH);
    scale = Math.max(0.15, Math.min(scale, 1.2));
    var tx = (w - OrgChartState.contentW * scale) / 2;
    var ty = (h - OrgChartState.contentH * scale) / 2;
    OrgChartState.zoom.scale(scale).translate([tx, ty]);
    d3.select('#OrgChartViewport').attr('transform', 'translate(' + tx + ',' + ty + ') scale(' + scale + ')');
    OrgChartUpdateMinimapLens(); // FIX: Update lensa saat fit
}

function OrgChartZoom(factor) {
    if (!OrgChartState.zoom) return;
    var scale = OrgChartState.zoom.scale() * factor;
    scale = Math.max(0.15, Math.min(scale, 3));
    OrgChartState.zoom.scale(scale);
    var tr = OrgChartState.zoom.translate();
    d3.select('#OrgChartViewport').attr('transform', 'translate(' + tr + ') scale(' + scale + ')');
    OrgChartUpdateMinimapLens(); // FIX: Update lensa saat zoom
}

function OrgChartPanelEmpty() {
    $('#OrgChartPanel').html('<p class="text-muted small mb-0">Klik salah satu kotak pada bagan.</p>');
}

function OrgChartSelect(id) {
    OrgChartState.selected = id;
    var n = OrgChartState.nodes[id];
    if (!n) return;

    // --- OTOMATIS BUKA CABANG PARENT YANG TERLIPAT ---
    OrgChartExpandAncestors(id);

    OrgChartState.selected = id;

    // Render ulang bagan terlebih dahulu agar cabang yang tadinya terlipat terbuka secara struktural
    OrgChartRender();

    // --- ANIMASI MEMUSATKAN KANVAS KE NODE YANG DICARI ---
    if (OrgChartState.zoom && n.px !== undefined && n.py !== undefined) {
        var wrap = document.getElementById('OrgChartWrap');
        var w = wrap.clientWidth || 900;
        var h = wrap.clientHeight || 600;

        // Koordinat aktual node di dalam viewport
        var nodeCenterX = n.px + OrgChartState.offsetX + (ORG_NODE_W / 2);
        var nodeCenterY = n.py + OrgChartState.offsetY + (ORG_NODE_H / 2);

        // Skala zoom saat ini (atau default 1 jika terlalu jauh)
        var scale = OrgChartState.zoom.scale();
        if (scale < 0.8) scale = 1; // Naikkan skala jika sedang terlalu kecil agar node terlihat jelas

        // Hitung translasi agar node tepat berada di tengah layar
        var tx = (w / 2) - (nodeCenterX * scale);
        var ty = (h / 2) - (nodeCenterY * scale);

        OrgChartState.zoom.scale(scale).translate([tx, ty]);

        // Jalankan transisi animasi mulus selama 500ms
        d3.select('#OrgChartViewport')
            .transition()
            .duration(500)
            .ease('cubic-out')
            .attr('transform', 'translate(' + tx + ',' + ty + ') scale(' + scale + ')');

        OrgChartUpdateMinimapLens();
    }

    // --- RENDER DETAIL PANEL SAMPING ---
    var options = '<option value="">-- Posisikan sebagai Root Utama --</option>';
    var ids = Object.keys(OrgChartState.nodes);
    for (var i = 0; i < ids.length; i++) {
        var other = OrgChartState.nodes[ids[i]];
        if (other.id === n.id) continue;
        var sel = (n.parent_id === other.id) ? ' selected' : '';
        options += '<option value="' + other.id + '"' + sel + '>' + orgEsc(other.position) + ' (' + orgEsc(other.name) + ')</option>';
    }

    var html = '<dl class="row mb-2 small">' +
        '  <dt class="col-4">Jabatan</dt><dd class="col-8 font-weight-bold">' + orgEsc(n.position) + '</dd>' +
        '  <dt class="col-4">Pegawai</dt><dd class="col-8">' + orgEsc(n.name) + '</dd>' +
        '  <dt class="col-4">NIK</dt><dd class="col-8">' + orgEsc(n.nik) + '</dd>' +
        '  <dt class="col-4">Departemen</dt><dd class="col-8">' + orgEsc(n.dept_name || n.department) + '</dd>' +
        '  <dt class="col-4">Direktorat</dt><dd class="col-8">' + orgEsc(n.directorat) + '</dd>' +
        '  <dt class="col-4">Status</dt><dd class="col-8">' + (n.is_vacant ? '<span class="text-danger font-weight-bold">VACANT</span>' : '<span class="text-success">Terisi</span>') + '</dd>' +
        '</dl>' +
        '<label class="small text-muted mb-1">Atasan Langsung (Posisi)</label>' +
        '<select id="OrgChartParent" class="form-control form-control-sm">' + options + '</select>' +
        (ORG_CAN_MANAGE
            ? '<button type="button" class="btn btn-primary btn-sm mt-2" onclick="OrgChartSaveParentPanel(' + n.id + ');">Simpan Atasan</button>'
            : '<div class="small text-muted mt-2">Hanya admin yang dapat mengubah atasan posisi.</div>') +
        '<div id="OrgChartSaveMsg" class="small mt-2"></div>';

    $('#OrgChartPanel').html(html);
    OrgChartRender();
}

function OrgChartSaveParentPanel(id) {
    var parentId = $('#OrgChartParent').val();
    OrgChartSaveParentDragDrop(id, parentId);
}

/* Render Minimap Presisi */
function OrgChartRenderMinimap(contentW, contentH, positioned, links, offsetX, offsetY) {
    var minimap = d3.select('#OrgMinimap');
    
    // Pastikan viewBox minimap memetakan seluruh area fisik kanvas (0 s/d contentW, 0 s/d contentH)
    minimap.attr('viewBox', '0 0 ' + contentW + ' ' + contentH);
    
    var miniGroup = d3.select('#OrgMinimapViewport');
    miniGroup.selectAll('*').remove();

    if (!contentW || !contentH) return;

    // Render Garis Penghubung pada Minimap
    for (var l = 0; l < links.length; l++) {
        var src = links[l].source;
        var tgt = links[l].target;
        if (src.id === '__root__' || tgt.id === '__root__') continue;
        
        var x1 = src.px + offsetX + (ORG_NODE_W / 2);
        var y1 = src.py + offsetY + (ORG_NODE_H / 2);
        var x2 = tgt.px + offsetX + (ORG_NODE_W / 2);
        var y2 = tgt.py + offsetY + (ORG_NODE_H / 2);

        miniGroup.append('line')
            .attr('x1', x1).attr('y1', y1)
            .attr('x2', x2).attr('y2', y2)
            .attr('stroke', '#cbd5e1').attr('stroke-width', 3);
    }

    // Render Kotak Node pada Minimap
    for (var k = 0; k < positioned.length; k++) {
        var nd = positioned[k];
        var data = OrgChartState.nodes[nd.id];
        if (!data) continue;
        
        miniGroup.append('rect')
            .attr('x', nd.px + offsetX)
            .attr('y', nd.py + offsetY)
            .attr('width', ORG_NODE_W)
            .attr('height', ORG_NODE_H)
            .attr('rx', 6)
            .attr('fill', data.is_vacant ? '#e74c3c' : orgDeptColor(data.department));
    }

    // Panggil pembaruan lensa posisi
    OrgChartUpdateMinimapLens();
}

/* Update Posisi Lensa Minimap (Kamera Viewport) */
function OrgChartUpdateMinimapLens() {
    if (!OrgChartState.zoom || !OrgChartState.contentW || !OrgChartState.contentH) return;

    var wrap = document.getElementById('OrgChartWrap');
    var w = wrap.clientWidth || 900;
    var h = wrap.clientHeight || 600;

    var currentTranslate = OrgChartState.zoom.translate();
    var currentScale = OrgChartState.zoom.scale();

    // Perhitungan Lensa berdasarkan Transformasi Viewport D3
    var lensX = -currentTranslate[0] / currentScale;
    var lensY = -currentTranslate[1] / currentScale;
    var lensW = w / currentScale;
    var lensH = h / currentScale;

    // Posisikan Lensa Biru tepat pada koordinat tampilan saat ini
    d3.select('#OrgMinimapLens')
        .attr('x', lensX)
        .attr('y', lensY)
        .attr('width', Math.max(15, lensW))
        .attr('height', Math.max(15, lensH));
}

function OrgChartExpandAncestors(id) {
    var currentId = id;
    var safetyCounter = 0;

    // Telusuri ke atas melalui parent_id sampai ke root tertinggi
    while (currentId && OrgChartState.nodes[currentId] && safetyCounter < 100) {
        var node = OrgChartState.nodes[currentId];
        var parentId = node.parent_id;

        // Jika parent memiliki anak dan berstatus collapsed, buka lipatannya
        if (parentId && OrgChartState.collapsed[parentId]) {
            delete OrgChartState.collapsed[parentId];
        }

        currentId = parentId;
        safetyCounter++;
    }
}

$(document).ready(function () {
    $('#OrgFilterDirectorat, #OrgFilterDepartment, #OrgOrientation').on('change', function () {
        if (this.id === 'OrgOrientation') { OrgChartRender(); return; }
        OrgChartLoad();
    });
    $('#OrgFilterSystem').on('change', function () { OrgChartLoad(); });

    OrgChartLoad();

    // Autocomplete Search
    $('#OrgSearchQuery').on('input', function () {
        var keyword = $(this).val().toLowerCase().trim();
        var dropdown = $('#OrgSearchDropdown');
        if (keyword.length < 2) { dropdown.hide().empty(); return; }

        var ids = Object.keys(OrgChartState.nodes);
        var matches = [];
        for (var i = 0; i < ids.length; i++) {
            var node = OrgChartState.nodes[ids[i]];
            if ((node.position || '').toLowerCase().indexOf(keyword) !== -1 ||
                (node.name || '').toLowerCase().indexOf(keyword) !== -1 ||
                (node.nik || '').toLowerCase().indexOf(keyword) !== -1) {
                matches.push(node);
                if (matches.length >= 10) break;
            }
        }

        if (matches.length === 0) { dropdown.hide().empty(); return; }

        var html = '';
        for (var j = 0; j < matches.length; j++) {
            var m = matches[j];
            html += '<a class="dropdown-item small py-2 px-3 border-bottom org-autocomplete-item" href="javascript:void(0);" data-id="' + m.id + '">' +
                        '<div class="font-weight-bold text-dark">' + orgEsc(m.position) + '</div>' +
                        '<div class="text-muted" style="font-size: 11px;">Pegawai: ' + orgEsc(m.name) + ' &bull; ' + orgEsc(m.department) + '</div>' +
                    '</a>';
        }
        dropdown.html(html).show();
    });

    $(document).on('click', '.org-autocomplete-item', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#OrgSearchDropdown').hide().empty();
        OrgChartSelect(id);
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#OrgSearchQuery, #OrgSearchDropdown').length) {
            $('#OrgSearchDropdown').hide();
        }
    });

    // Minimap Click Pan
    $('#OrgMinimap').off('click').on('click', function (e) {
        if (!OrgChartState.zoom || !OrgChartState.contentW || !OrgChartState.contentH) return;

        var svgEl = document.getElementById('OrgMinimap');
        var rect = svgEl.getBoundingClientRect();

        // Hitung posisi relatif klik kursor di dalam elemen minimap (0.0 sampai 1.0)
        var clickXRatio = (e.clientX - rect.left) / rect.width;
        var clickYRatio = (e.clientY - rect.top) / rect.height;

        // Konversi ke koordinat penuh Kanvas Utama
        var targetCanvasX = clickXRatio * OrgChartState.contentW;
        var targetCanvasY = clickYRatio * OrgChartState.contentH;

        var wrap = document.getElementById('OrgChartWrap');
        var w = wrap.clientWidth || 900;
        var h = wrap.clientHeight || 600;

        var scale = OrgChartState.zoom.scale();

        // Hitung translasi agar titik klik tepat menjadi pusat tampilan
        var tx = (w / 2) - (targetCanvasX * scale);
        var ty = (h / 2) - (targetCanvasY * scale);

        OrgChartState.zoom.scale(scale).translate([tx, ty]);

        // Animasi perpindahan kanvas & perbarui lensa minimap
        d3.select('#OrgChartViewport')
            .transition()
            .duration(350)
            .ease('cubic-out')
            .attr('transform', 'translate(' + tx + ',' + ty + ') scale(' + scale + ')')
            .each('end', function() {
                OrgChartUpdateMinimapLens();
            });

        OrgChartUpdateMinimapLens();
    });
});
</script>
@endpush