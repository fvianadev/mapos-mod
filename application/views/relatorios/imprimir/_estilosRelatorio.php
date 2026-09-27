<style type="text/css">
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        color: #222;
    }

    .rel-titulo {
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        margin: 4px 0 10px 0;
    }

    /* Tabela dos relatórios: fechamento completo das linhas de grade.
       O mPDF não pinta as bordas externas com border-collapse: collapse
       (linha some na direita/inferior), então a grade é montada com
       célula = direita + inferior e tabela = topo + esquerda. */
    .rel-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 10px;
        border-top: 1px solid #b9bcc7 !important;
        border-left: 1px solid #b9bcc7 !important;
    }

    .rel-table th,
    .rel-table td {
        border-right: 1px solid #b9bcc7 !important;
        border-bottom: 1px solid #b9bcc7 !important;
        padding: 4px 5px;
        vertical-align: middle;
        page-break-inside: avoid;
    }

    /* Cabeçalho das colunas: fundo suave (equivalente a #2d335b com
       10% de opacidade sobre branco — cor sólida para o mPDF) */
    .rel-table thead th {
        background-color: #eceef5;
        color: #2d335b;
        font-weight: bold;
        text-align: center;
        font-size: 10px;
    }

    .rel-table td.num {
        text-align: right;
    }

    .rel-table td.ctr {
        text-align: center;
    }

    .rel-table tfoot td,
    .rel-table tr.rel-total td {
        border-top: 2px solid #8a8f9e !important;
        font-weight: bold;
        font-size: 11px;
        text-align: right;
        padding: 5px;
    }

    .rel-table tfoot td.ctr,
    .rel-table tr.rel-total td.ctr {
        text-align: center;
    }

    .rel-table tr.rel-total td {
        background-color: #f5f6fa;
    }

    .rel-verde {
        color: #1a7f37;
    }

    .rel-vermelho {
        color: #c62828;
    }

    .rel-vazio {
        color: #777;
        text-align: center;
        font-style: italic;
    }

    .rel-data {
        text-align: right;
        margin-top: 12px;
        font-size: 11px;
    }
</style>
