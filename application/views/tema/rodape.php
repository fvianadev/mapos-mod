<div class="row-fluid">
    <div id="footer" class="span12">
        <a class="pecolor" href="https://github.com/RamonSilva20/mapos" target="_blank">
            <?= date('Y') ?> &copy; Ramon Silva - Map-OS - Versão: <?= $this->config->item('app_version') ?>
        </a>
    </div>
</div>
<!--end-Footer-part-->
<script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
<script src="<?= base_url() ?>assets/js/matrix.js"></script>
</body>
<style>
    .dataTables_wrapper .dataTables_filter,
    .dataTables_filter,
    #tabela_filter {
        position: relative !important;
        top: auto !important;
        right: auto !important;
        bottom: auto !important;
        left: auto !important;
        float: right !important;
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        color: #444 !important;
        font-size: 13px !important;
    }
    .dataTables_filter label {
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        font-weight: 500 !important;
        color: #444 !important;
        font-size: 13px !important;
        white-space: nowrap !important;
    }
    .dataTables_filter input,
    .dataTables_filter input[type="search"] {
        margin: 0 0 0 6px !important;
        height: 32px !important;
        padding: 4px 10px !important;
        border-radius: 4px !important;
        border: 1px solid #ccc !important;
        font-size: 13px !important;
        box-sizing: border-box !important;
        display: inline-block !important;
    }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        var dataTableEnabled = '<?= $configuration['control_datatable'] ?>';
        if(dataTableEnabled == '1') {
            var moveFilter = function() {
                var $filter = $('.dataTables_filter');
                if ($filter.length > 0) {
                    if ($('#custom-search-container').length > 0) {
                        $('#custom-search-container').html($filter);
                    } else {
                        var $container = $('<div class="table-search-header" style="display: flex; justify-content: flex-end; margin-bottom: 10px;"></div>');
                        $container.append($filter);
                        $('#tabela').closest('.widget-box').before($container);
                    }
                }
            };

            $('#tabela').dataTable( {
                "pageLength": <?= $configuration['per_page'] ?>,
                "bLengthChange": false,
                "ordering": false,
                "info": false,
                "language": {
                    "url": "<?= base_url() ?>assets/js/dataTable_pt-br.json",
                },
                "oLanguage": {
                    "sSearch": "Pesquisa rápida na tabela abaixo:"
                },
                "initComplete": function() {
                    moveFilter();
                }
            } );
            moveFilter();

            $(document).on('keyup input search', '.dataTables_filter input, #custom-search-container input', function() {
                var val = $(this).val();
                if ($.fn.DataTable.isDataTable('#tabela')) {
                    $('#tabela').DataTable().search(val).draw();
                }
            });
        }

        var currentPerPage = '<?= $configuration['per_page'] ?>' || '20';
        var perPageSelectorHtml = `
            <div class="per-page-wrapper" style="display: inline-flex; align-items: center; margin: 8px 0; font-size: 13px; color: #555;">
                <label for="select-per-page" style="margin: 0 8px 0 0; font-weight: 600; font-size: 13px; line-height: 30px; color: #444;">Exibir por página:</label>
                <select id="select-per-page" style="width: auto; margin: 0; height: 30px; padding: 2px 8px; font-size: 13px; border-radius: 4px; border: 1px solid #ccc; background-color: #fff; cursor: pointer;">
                    <option value="10" ${currentPerPage == '10' ? 'selected' : ''}>10</option>
                    <option value="20" ${currentPerPage == '20' ? 'selected' : ''}>20</option>
                    <option value="30" ${currentPerPage == '30' ? 'selected' : ''}>30</option>
                    <option value="50" ${currentPerPage == '50' ? 'selected' : ''}>50</option>
                    <option value="100" ${currentPerPage == '100' ? 'selected' : ''}>100</option>
                </select>
            </div>
        `;

        if ($('.pagination').length > 0) {
            var $pag = $('.pagination').first();
            $pag.css({
                'display': 'flex',
                'justify-content': 'space-between',
                'align-items': 'center',
                'flex-wrap': 'wrap',
                'margin-top': '10px'
            }).prepend(perPageSelectorHtml);
        } else if ($('#tabela').length > 0) {
            $('#tabela').closest('.widget-box').after('<div class="pagination-footer-fallback" style="display:flex; justify-content: flex-start; margin-top: 10px;">' + perPageSelectorHtml + '</div>');
        }

        $(document).on('change', '#select-per-page', function() {
            var newPerPage = $(this).val();
            $.ajax({
                url: '<?= site_url('mapos/set_per_page') ?>',
                type: 'POST',
                data: { per_page: newPerPage },
                dataType: 'json',
                success: function(response) {
                    if (response.result) {
                        var cleanUrl = window.location.pathname.replace(/\/(\d+)$/, '') + window.location.search;
                        window.location.href = cleanUrl;
                    }
                }
            });
        });
    } );
</script>
</html>
