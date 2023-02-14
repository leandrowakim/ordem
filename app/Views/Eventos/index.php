<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo'); ?> <?php echo $titulo; ?> <?php echo $this->endSection(); ?>

<?php echo $this->section('estilos'); ?>

<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/fullcalendar/fullcalendar.min.css') ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo site_url('recursos/vendor/fullcalendar/toastr.min.css') ?>" />

<style>
   .fc-event, .fc-event-dot {background-color: #343a40 !important;}
</style>

<?php echo $this->endSection(); ?>

<?php echo $this->section('conteudo'); ?>

<div id="calendario" class="container-fluid">
   <!-- Aqui será renderizado o fullcalendar -->
</div>

<?php echo $this->endSection(); ?>

<?php echo $this->section('scripts'); ?>

<script type="text/javascript" src="<?php echo site_url('recursos/vendor/fullcalendar/fullcalendar.min.js') ?>"></script>
<script type="text/javascript" src="<?php echo site_url('recursos/vendor/fullcalendar/toastr.min.js') ?>"></script>
<script type="text/javascript" src="<?php echo site_url('recursos/vendor/fullcalendar/moment.min.js') ?>"></script>

<script>

   $(document).ready(function(){

      var calendario = $("#calendario").fullCalendar({
         
         ignoreTimezone: false,
         monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
         monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
         dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sabado'],
         dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
         buttonText: {
            today: "Hoje",
            month: "Mês",
            week: "Semana",
            day: "Dia"
         },

         header: {
            left: 'prev, next today',
            center: 'title',
            right: 'month',
         },
         height: 580,
         editable: true,
         events: '<?php echo site_url('eventos/eventos'); ?>',
         displayEventTime: false,
         selectable: true,
         selectHelper: true,
         select: function(start, end, allDay){

            var title = prompt('Informe o título do evento');
            if (title) {
               
               var start = $.fullCalendar.formatDate(start, 'Y-MM-DD');
               var end = $.fullCalendar.formatDate(end, 'Y-MM-DD');

               $.ajax({
                  url: '<?php echo site_url('eventos/cadastrar'); ?>',
                  type: 'GET',
                  data:{
                     title: title,
                     start: start,
                     end: end,
                  },
                  success: function(response){
                     exibeMensagem('Criado com sucesso!');

                     calendario.fullCalendar('renderEvent', {
                        id: response.id,
                        title: title,
                        start: start,
                        end: end,
                        allDay: allDay,                        
                     }, true);
                     calendario.fullCalendar('unselect');
                  },
               });
            }  //Fim if title
         },
         //Atualizando o evento
         eventDrop: function(event, delta, revertFunc){

            if (event.conta_id || event.ordem_id) {

               alert('Não é possível alterar esse evento do sistema');
               return revertFunc();
            } else {
               
               var start = $.fullCalendar.formatDate(event.start, 'Y-MM-DD');
               var end = $.fullCalendar.formatDate(event.end, 'Y-MM-DD');

               $.ajax({

                  url: '<?php echo site_url('eventos/atualizar/'); ?>' + event.id,
                  type: 'GET',
                  data:{
                     start:start,
                     end:end,
                  },
                  success: function(response){

                     exibeMensagem('Atualizado com sucesso!');
                  },
               });
            }

         },
         //Excluíndo o evento
         eventClick: function(event){
            if (event.conta_id || event.ordem_id) {

               alert(event.title + '\r\n\r' + 'Não é possível excluír esse evento do sistema');
            } else {

               var exibeEvento = confirm(event.title + '\r\n\r' + 'Deseja excluír esse evento?');
               
               if (exibeEvento) {
                  $.ajax({

                     url: '<?php echo site_url('eventos/excluir'); ?>',
                     type: 'GET',
                     data:{
                        id: event.id,
                     },
                     success: function(response){

                        calendario.fullCalendar('removeEvents', event.id);
                        exibeMensagem('Excluído com sucesso!');
                     },
                  });
               }
            }
         }

      });

   });

   function exibeMensagem(mensagem){
      toastr.success(mensagem, 'Evento');
   }

</script>

<?php echo $this->endSection(); ?>