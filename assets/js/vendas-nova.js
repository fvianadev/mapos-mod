/* Nova Venda - comportamento da tela única (sem etapas) */
(function ($) {
    'use strict';

    var cfg = window.VN || {};
    var itens = [];
    var ultimoResultado = [];
    var consultaAtiva = false;
    var adicionarAoResponder = false;
    var clienteSelecionado = '';
    var vendedorSelecionado = '';
    var enviando = false;

    var $form = $('#formNovaVenda');
    var $busca = $('#vn-busca-produto');
    var $corpo = $('#vn-itens-corpo');
    var $empty = $('#vn-empty');
    var $alertas = $('#vn-alertas');

    function escapeHtml(valor) {
        return String(valor === null || valor === undefined ? '' : valor)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function parseNum(valor) {
        if (typeof valor === 'number') {
            return isFinite(valor) ? valor : 0;
        }
        var texto = String(valor === null || valor === undefined ? '' : valor).trim()
            .replace(/[^\d.,-]/g, '');

        if (texto.indexOf(',') > -1 && texto.indexOf('.') > -1) {
            texto = texto.replace(/\./g, '').replace(',', '.');
        } else {
            texto = texto.replace(',', '.');
        }

        var numero = parseFloat(texto);
        return isNaN(numero) ? 0 : numero;
    }

    function brl(valor) {
        return 'R$ ' + Number(valor).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function htmlParaTexto(html) {
        var tmp = String(html === null || html === undefined ? '' : html)
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<\/(p|div|li|h[1-6])>/gi, '\n');
        var div = document.createElement('div');
        div.innerHTML = tmp;

        var texto = div.textContent || div.innerText || '';

        return texto.replace(/\n{3,}/g, '\n\n').trim();
    }

    function textoParaHtml(texto) {
        var value = String(texto === null || texto === undefined ? '' : texto).replace(/\r\n/g, '\n');

        return escapeHtml(value).replace(/\n/g, '<br>');
    }

    function aviso(html, tipo) {
        $alertas.html('<div class="vn-msg vn-msg--' + (tipo || 'warn') + '"><i class="bx bx-info-circle"></i><span>' + html + '</span></div>');
    }

    function limparAviso() {
        $alertas.empty();
    }

    function totais() {
        var subtotal = 0;
        itens.forEach(function (item) {
            subtotal += item.quantidade * item.preco;
        });

        var bruto = parseNum($('#desconto').val());
        var percentual = $('#tipoDesconto').val() === 'porcento';
        var desconto = percentual ? subtotal * (bruto / 100) : bruto;
        desconto = Math.min(subtotal, Math.max(0, desconto));

        return {
            subtotal: subtotal,
            desconto: desconto,
            liquido: subtotal - desconto,
            percentual: percentual,
            bruto: bruto
        };
    }

    function problemasDeEstoque() {
        if (!cfg.controlEstoque) {
            return [];
        }

        var problemas = [];
        itens.forEach(function (item) {
            if (item.estoque !== null && item.estoque !== undefined && item.quantidade > item.estoque) {
                problemas.push('Estoque insuficiente para “' + escapeHtml(item.descricao) + '” (disponível: ' + escapeHtml(item.estoque) + ').');
            }
        });

        return problemas;
    }

    function atualizarResumo() {
        var t = totais();
        $('#vn-subtotal').text(brl(t.subtotal));
        $('#vn-total').text(brl(t.liquido));
        $('#vn-faturar-total').text(brl(t.liquido));

        if (t.desconto > 0) {
            $('#vn-desconto-info').text('Desconto: -' + brl(t.desconto) + (t.percentual ? ' (' + t.bruto + ' %)' : ''));
        } else {
            $('#vn-desconto-info').text('');
        }
    }

    function render() {
        var html = '';

        itens.forEach(function (item, indice) {
            var total = item.quantidade * item.preco;
            var estoqueBaixo = cfg.controlEstoque && item.estoque !== null &&
                item.estoque !== undefined && item.quantidade > item.estoque;

            html += '<tr data-indice="' + indice + '">'
                + '<td><span class="vn-item-nome">' + escapeHtml(item.descricao) + '</span>'
                + (item.codDeBarra ? '<span class="vn-item-cod">Cód. ' + escapeHtml(item.codDeBarra) + '</span>' : '')
                + (estoqueBaixo ? '<span class="vn-item-cod vn-estoque-baixo">Estoque disponível: ' + escapeHtml(item.estoque) + '</span>' : '')
                + '</td>'
                + '<td class="vn-num"><input type="text" inputmode="numeric" class="vn-qtd" value="' + escapeHtml(item.quantidade) + '" aria-label="Quantidade de ' + escapeHtml(item.descricao) + '" /></td>'
                + '<td class="vn-num"><input type="text" inputmode="decimal" class="vn-preco" value="' + escapeHtml(item.preco) + '" aria-label="Preço de ' + escapeHtml(item.descricao) + '" /></td>'
                + '<td class="vn-num vn-item-total">' + brl(total) + '</td>'
                + '<td class="vn-act"><button type="button" class="vn-btn-del" title="Remover item" aria-label="Remover ' + escapeHtml(item.descricao) + '"><i class="bx bx-trash"></i></button></td>'
                + '</tr>';
        });

        $corpo.html(html);

        var quantidade = itens.length;
        $('#vn-itens-count').text(quantidade + (quantidade === 1 ? ' item' : ' itens'));
        $empty.toggleClass('is-visible', quantidade === 0);
        $empty.toggle(quantidade === 0);
        $('.vn-table').toggle(quantidade > 0);

        var problemas = problemasDeEstoque();
        if (problemas.length) {
            aviso(problemas.join('<br />'), 'warn');
        } else {
            limparAviso();
        }

        atualizarResumo();
    }

    function adicionarProduto(produto) {
        if (!produto || !produto.id) {
            return;
        }

        var existente = null;
        itens.forEach(function (item) {
            if (String(item.produtos_id) === String(produto.id)) {
                existente = item;
            }
        });

        var estoque = produto.estoque === undefined || produto.estoque === null ? null : parseNum(produto.estoque);
        var quantidade = existente ? existente.quantidade + 1 : 1;

        if (cfg.controlEstoque && estoque !== null && quantidade > estoque) {
            aviso('Estoque insuficiente para “' + escapeHtml(produto.descricao || produto.label) + '” (disponível: ' + estoque + ').', 'error');
            return;
        }

        if (existente) {
            existente.quantidade = quantidade;
        } else {
            itens.push({
                produtos_id: produto.id,
                descricao: produto.descricao || produto.label || '',
                codDeBarra: produto.codDeBarra || '',
                preco: parseNum(produto.preco),
                quantidade: quantidade,
                estoque: estoque
            });
        }

        $busca.val('');
        render();
        $busca.focus();
    }

    function normalizarItensIniciais() {
        var iniciais = cfg.itensIniciais || [];
        iniciais.forEach(function (item) {
            itens.push({
                produtos_id: item.produtos_id,
                descricao: item.descricao || 'Produto',
                codDeBarra: item.codDeBarra || '',
                preco: parseNum(item.preco),
                quantidade: parseInt(item.quantidade, 10) || 1,
                estoque: item.estoque === undefined || item.estoque === null ? null : parseNum(item.estoque)
            });
        });
    }

    /* Busca de produto (nome ou código de barras) */
    $busca.autocomplete({
        minLength: 1,
        delay: 120,
        source: function (request, response) {
            consultaAtiva = true;
            $.getJSON(cfg.buscaProduto, { term: request.term })
                .done(function (dados) {
                    consultaAtiva = false;
                    ultimoResultado = dados || [];
                    response(ultimoResultado);

                    if (adicionarAoResponder) {
                        adicionarAoResponder = false;
                        if (ultimoResultado.length === 1) {
                            adicionarProduto(ultimoResultado[0]);
                        } else if (ultimoResultado.length > 1) {
                            aviso('Mais de um produto encontrado — escolha na lista.', 'warn');
                        } else {
                            aviso('Nenhum produto encontrado para “' + escapeHtml(request.term) + '”.', 'error');
                        }
                    }
                })
                .fail(function () {
                    consultaAtiva = false;
                    ultimoResultado = [];
                    response([]);
                });
        },
        select: function (event, ui) {
            if (ui.item && ui.item.id) {
                adicionarProduto(ui.item);
                return false;
            }
            return true;
        }
    });

    $busca.on('keydown', function (event) {
        if (event.keyCode !== 13) {
            return;
        }

        var instancia = $busca.autocomplete('instance');
        if (instancia && instancia.menu && instancia.menu.active) {
            return;
        }

        event.preventDefault();

        if (consultaAtiva) {
            adicionarAoResponder = true;
            return;
        }

        if (ultimoResultado.length) {
            adicionarProduto(ultimoResultado[0]);
        } else {
            aviso('Digite o nome ou o código de barras do produto.', 'warn');
        }
    });

    /* Ações da tabela de itens (delegadas) */
    $corpo.on('change input', '.vn-qtd, .vn-preco', function (event) {
        var $linha = $(this).closest('tr');
        var indice = parseInt($linha.data('indice'), 10);
        var item = itens[indice];

        if (!item) {
            return;
        }

        if ($(this).hasClass('vn-qtd')) {
            var quantidade = Math.floor(parseNum($(this).val()));
            item.quantidade = quantidade > 0 ? quantidade : 1;
        } else {
            item.preco = Math.max(0, parseNum($(this).val()));
        }

        $linha.find('.vn-item-total').text(brl(item.quantidade * item.preco));
        atualizarResumo();

        if (event.type === 'change') {
            render();
        }
    });

    $corpo.on('click', '.vn-btn-del', function () {
        var indice = parseInt($(this).closest('tr').data('indice'), 10);
        itens.splice(indice, 1);
        render();
        $busca.focus();
    });

    /* Cliente e vendedor */
    $('#cliente').autocomplete({
        minLength: 1,
        source: cfg.autoCompleteCliente,
        select: function (event, ui) {
            if (ui.item && ui.item.id) {
                $('#clientes_id').val(ui.item.id);
                clienteSelecionado = ui.item.label;
            } else {
                $('#clientes_id').val('');
                clienteSelecionado = '';
                aviso('Cliente não encontrado no cadastro — use “Novo cliente” para cadastrar.', 'warn');
            }
            return true;
        }
    });

    $('#cliente').on('input', function () {
        if (clienteSelecionado && $(this).val() !== clienteSelecionado) {
            $('#clientes_id').val('');
            clienteSelecionado = '';
        }
    });

    $('#tecnico').autocomplete({
        minLength: 1,
        source: cfg.autoCompleteUsuario,
        select: function (event, ui) {
            if (ui.item && ui.item.id) {
                $('#usuarios_id').val(ui.item.id);
                vendedorSelecionado = ui.item.label;
            }
            return true;
        }
    });

    $('#tecnico').on('input', function () {
        if (vendedorSelecionado && $(this).val() !== vendedorSelecionado) {
            $('#usuarios_id').val('');
            vendedorSelecionado = '';
        }
    });

    /* Resumo ao vivo */
    $('#desconto, #tipoDesconto').on('input change', atualizarResumo);

    /* Status em controle segmentado */
    $('.vn-seg input').on('change', function () {
        $('.vn-seg').removeClass('is-active');
        $(this).closest('.vn-seg').addClass('is-active');
    });

    /* Observações: acordeão, abas e crescimento automático */
    function autoCrescer(campo) {
        campo.style.height = 'auto';
        campo.style.height = Math.min(campo.scrollHeight + 2, 340) + 'px';
    }

    $('#vn-obs-toggle').on('click', function () {
        var aberto = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', aberto ? 'false' : 'true');
        $('#vn-obs-corpo').prop('hidden', aberto);
        if (!aberto) {
            var ativo = $('.vn-obs:not(.vn-is-hidden)').get(0);
            if (ativo) {
                autoCrescer(ativo);
            }
        }
    });

    $('.vn-tab').on('click', function () {
        destruirEditor();
        $('.vn-tab').removeClass('is-active');
        $(this).addClass('is-active');

        var alvo = $(this).data('obs');
        $('.vn-obs').each(function () {
            $(this).toggleClass('vn-is-hidden', this.id !== alvo);
        });

        var ativo = $('.vn-obs:not(.vn-is-hidden)').get(0);
        if (ativo) {
            autoCrescer(ativo);
        }
    });

    $('.vn-obs').on('input', function () {
        autoCrescer(this);
    });

    function temEditor($campo) {
        var instancia = $campo.data('trumbowyg');

        return !!instancia && typeof instancia === 'object';
    }

    function destruirEditor() {
        $('.vn-obs').each(function () {
            var $campo = $(this);

            if (!temEditor($campo)) {
                return;
            }

            var instancia = $campo.data('trumbowyg');

            if (typeof instancia.syncCode === 'function') {
                instancia.syncCode();
            }

            var html = typeof instancia.html === 'function' ? instancia.html() : $campo.val();

            $campo.data('vn-html', html);
            $campo.trumbowyg('destroy');
            $campo.val(htmlParaTexto(html));
        });

        $('#vn-formatar').removeClass('is-active');
    }

    function restaurarHtml($campo) {
        var salvo = $campo.data('vn-html');

        if (!salvo) {
            return;
        }

        if ($campo.val().trim() === htmlParaTexto(salvo)) {
            $campo.val(salvo);
        } else {
            $campo.data('vn-html', null);
        }
    }

    function prepararObservacoesParaEnvio() {
        destruirEditor();

        $('.vn-obs').each(function () {
            restaurarHtml($(this));
        });
    }

    function normalizarObservacoesIniciais() {
        $('.vn-obs').each(function () {
            var $campo = $(this);
            var valor = String($campo.val() || '');

            if (/<[a-z][^>]*>/i.test(valor)) {
                $campo.data('vn-html', valor);
                $campo.val(htmlParaTexto(valor));
            }
        });
    }

    $('#vn-formatar').on('click', function () {
        var $alvo = $('.vn-obs:not(.vn-is-hidden)').first();
        if (!$alvo.length) {
            return;
        }

        if (temEditor($alvo)) {
            destruirEditor();
            return;
        }

        destruirEditor();
        $alvo.trumbowyg({ lang: 'pt_br' });

        var instancia = $alvo.data('trumbowyg');
        if (instancia && typeof instancia.html === 'function') {
            var salvo = $alvo.data('vn-html');
            var conteudo = (salvo && $alvo.val().trim() === htmlParaTexto(salvo)) ? salvo : textoParaHtml($alvo.val());
            instancia.html(conteudo);
        }

        $('#vn-formatar').addClass('is-active');

        var ativo = $alvo.get(0);
        if (ativo) {
            autoCrescer(ativo);
        }
    });

    /* Validação do formulário */
    $form.validate({
        ignore: [],
        rules: {
            status: { required: true },
            clientes_id: { required: true, number: true },
            dataVenda: { required: true },
            usuarios_id: { required: true, number: true }
        },
        messages: {
            status: 'Escolha o status da venda.',
            clientes_id: 'Selecione o cliente na lista.',
            dataVenda: 'Informe a data da venda.',
            usuarios_id: 'Informe o vendedor.'
        },
        errorClass: 'vn-field-error-text',
        errorPlacement: function (erro, elemento) {
            var nome = elemento.attr('name');

            if (nome === 'clientes_id') {
                erro.insertAfter('#cliente');
            } else if (nome === 'usuarios_id') {
                erro.insertAfter('#tecnico');
            } else if (nome === 'status') {
                erro.insertAfter('.vn-segmented');
            } else {
                erro.insertAfter(elemento);
            }
        },
        submitHandler: function (form) {
            if (enviando) {
                return false;
            }

            if (!itens.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sem itens',
                    text: 'Adicione ao menos um produto antes de salvar.'
                });
                $busca.focus();
                return false;
            }

            var problemas = problemasDeEstoque();
            if (problemas.length) {
                aviso(problemas.join('<br />'), 'error');
                return false;
            }

            var normalizados = itens.map(function (item) {
                return {
                    produtos_id: item.produtos_id,
                    descricao: item.descricao,
                    codDeBarra: item.codDeBarra,
                    estoque: item.estoque,
                    quantidade: item.quantidade,
                    preco: item.preco
                };
            });

            $('#itens_json').val(JSON.stringify(normalizados));
            $('#desconto').val(totais().bruto);

            prepararObservacoesParaEnvio();

            enviando = true;
            $('.vn-btn, #btnSalvar, #btnFaturarAgora, #vn-confirmar-faturar').prop('disabled', true).addClass('is-busy');
            $('#btnSalvar span').text('Salvando...');

            form.submit();
            return false;
        }
    });

    $('#btnSalvar').on('click', function () {
        $('#faturar').val('0');
    });

    $('#btnFaturarAgora').on('click', function () {
        if (!$form.valid()) {
            return;
        }

        if (!itens.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Sem itens',
                text: 'Adicione ao menos um produto antes de faturar.'
            });
            return;
        }

        var problemas = problemasDeEstoque();
        if (problemas.length) {
            aviso(problemas.join('<br />'), 'error');
            return;
        }

        $('#vn-vencimento').val($('#vn-vencimento').val() || new Date().toLocaleDateString('pt-BR'));
        $('#vn-modal-faturar').modal('show');
    });

    $('#vn-modal-faturar').on('hidden hidden.bs.modal', function () {
        if ($('#vn-confirmar-faturar').prop('disabled') !== true) {
            $('#faturar').val('0');
        }
    });

    $('#vn-recebido').on('change', function () {
        var recebido = $(this).is(':checked');
        $('#vn-campos-recebimento').toggleClass('vn-is-hidden', !recebido);
        if (recebido && !$('#vn-recebimento').val()) {
            $('#vn-recebimento').val(new Date().toLocaleDateString('pt-BR'));
        }
    });

    $('#vn-confirmar-faturar').on('click', function (event) {
        if (!$('#vn-vencimento').val()) {
            event.preventDefault();
            $('#vn-vencimento').addClass('is-error').focus();
            aviso('Informe a data de entrada para faturar.', 'error');
            return false;
        }

        $('#vn-vencimento').removeClass('is-error');
        $('#faturar').val('1');
        return true;
    });

    /* Atalho: Ctrl+Enter salva */
    $(document).on('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.keyCode === 13) {
            event.preventDefault();
            $form.valid() && $form.submit();
        }
    });

    /* Datas */
    $('.vn-datepicker').datepicker({ dateFormat: 'dd/mm/yy' });

    normalizarObservacoesIniciais();
    normalizarItensIniciais();
    render();
    $('.vn-obs').each(function () {
        autoCrescer(this);
    });
})(jQuery);
