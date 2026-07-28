<!DOCTYPE html>
<html lang="pt-br">

<head>
  <title><?= $this->config->item('app_name') ?> — Primeiro Acesso</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap-responsive.min.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/matrix-login.css" />
  <link href="<?= base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>assets/img/favicon.png" />
</head>

<body>
  <div class="main-login">
    <div class="left-login">
      <h1 class="h-one">Bem-vindo ao <?= $this->config->item('app_name') ?></h1>
      <h2 class="h-two">Configure seu usuário administrador</h2>
      <img src="<?php echo base_url() ?>assets/img/dashboard-animate.svg" class="left-login-image" alt="Map-OS">
    </div>
    <form class="form-vertical" id="formFirstAccess" method="post" action="<?= site_url('login/firstAccess') ?>">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <div class="d-flex flex-column">
        <div class="right-login">
          <div class="container">
            <div class="card">
              <div class="content">
                <div id="newlog">
                  <div class="icon2">
                    <img src="<?php echo base_url() ?>assets/img/logo-two.png">
                  </div>
                  <div class="title01">
                    <?= '<img src="' . base_url() . 'assets/img/logo-mapos-branco.png">'; ?>
                  </div>
                </div>
                <div class="input-field">
                  <label class="fas fa-user" for="nome"></label>
                  <input id="nome" name="nome" type="text" placeholder="Nome completo">
                </div>
                <div class="input-field">
                  <label class="fas fa-envelope" for="email"></label>
                  <input id="email" name="email" type="text" placeholder="Email">
                </div>
                <div class="input-field">
                  <label class="fas fa-lock" for="senha"></label>
                  <input id="senha" name="senha" type="password" placeholder="Senha">
                </div>
                <div class="input-field">
                  <label class="fas fa-lock" for="senha2"></label>
                  <input id="senha2" name="senha2" type="password" placeholder="Confirmar senha">
                </div>
                <div class="center">
                  <button id="btn-criar">Criar Administrador</button>
                </div>
                <div class="links-uteis"><a href="https://github.com/RamonSilva20/mapos">
                    <p><?= date('Y'); ?> &copy; Ramon Silva</p>
                  </a>
                </div>
                <a href="#notification" id="call-modal" role="button" class="btn" data-toggle="modal" style="display: none">notification</a>
                <div id="notification" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                  <div class="modal-header">
                    <h4 id="myModalLabel">Map-OS</h4>
                  </div>
                  <div class="modal-body">
                    <h5 style="text-align: center" id="message"></h5>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Fechar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script src="<?= base_url() ?>assets/js/jquery-1.12.4.min.js"></script>
  <script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url() ?>assets/js/validate.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('#nome').focus();
      $("#formFirstAccess").validate({
        rules: {
          nome: { required: true },
          email: { required: true, email: true },
          senha: { required: true, minlength: 6 },
          senha2: { required: true, equalTo: '#senha' }
        },
        messages: {
          nome: { required: '' },
          email: { required: '', email: 'Insira um email válido' },
          senha: { required: '', minlength: 'Mínimo 6 caracteres' },
          senha2: { required: '', equalTo: 'Senhas não conferem' }
        },
        submitHandler: function(form) {
          var dados = $(form).serialize();
          $('#btn-criar').addClass('disabled');

          $.ajax({
            type: "POST",
            url: "<?= site_url('login/firstAccess?ajax=true'); ?>",
            data: dados,
            dataType: 'json',
            success: function(data) {
              if (data.result == true) {
                window.location.href = "<?= site_url('mapos'); ?>";
              } else {
                $('#btn-criar').removeClass('disabled');
                $('#message').text(data.message || 'Erro ao criar administrador.');
                $('#call-modal').trigger('click');
                var newCsrfToken = data.MAPOS_TOKEN;
                $("input[name='<?= $this->security->get_csrf_token_name(); ?>']").val(newCsrfToken);
              }
            }
          });

          return false;
        },
        errorClass: "help-inline",
        errorElement: "span",
        highlight: function(element) {
          $(element).parents('.control-group').addClass('error');
        },
        unhighlight: function(element) {
          $(element).parents('.control-group').removeClass('error');
          $(element).parents('.control-group').addClass('success');
        }
      });
    });
  </script>
</body>

</html>
