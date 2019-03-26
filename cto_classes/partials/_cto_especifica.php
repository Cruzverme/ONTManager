<div class="form-group">
  <label for="ctoName">Nome CTO</label>
  <input id="ctoName" class="form-control" placeholder="CTO" name="cto" type="text" pattern="[a-zA-Z0-9.%]+" 
  title="Preencha a CTO personalizada" autofocus required>
</div>

<div class="form-group">
<label>PON</label> 
  <select class="form-control" name="pon">
    <?php 
        $sql_check = "SELECT DISTINCT * FROM ctos WHERE pon_id_fk = $olt";
        $executa_check = mysqli_query($conectar,$sql_check);
        if(mysqli_num_rows($executa_check) > 0) //checa se ja existe CTO cadastrada na pon, se existir ele vai tratar limitaçoes
        {
          $sql_consulta_serial = "SELECT DISTINCT olt.frame,olt.slot,olt.porta FROM pon olt
            INNER JOIN ctos cto ON cto.pon_id_fk = $olt
            WHERE olt.pon_id = $olt";//"SELECT frame,slot,porta FROM pon WHERE pon_id = $olt";
          
          $array_ctos = array();

          $executa_query = mysqli_query($conectar,$sql_consulta_serial) or die(mysqli_error($conectar));

          $sql_get_fsp = "SELECT DISTINCT caixa_atendimento,frame_slot_pon FROM `ctos`
            WHERE pon_id_fk = $olt ORDER BY frame_slot_pon" ; //SELECT PARA SELECIONAR AS CTOS DAS CELULAS
          $executa_get_fsp = mysqli_query($conectar,$sql_get_fsp);

          while ($porta_pon_cadastrada = mysqli_fetch_array($executa_get_fsp,MYSQLI_BOTH))
          {
            array_push($array_ctos,$porta_pon_cadastrada['frame_slot_pon']);
          }
          
          $conta = array_count_values($array_ctos);//conta quantas CTOs tem cadastradas na PON, devido a ter apenas 2 celulas em cada porta PON
          while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
          {
            for($porta = 0;$porta < $ont['porta'];$porta++)
            {
              if($conta["$ont[frame]-$ont[slot]-$porta"] < 16)
                echo "<option value=$olt-$ont[frame]-$ont[slot]-$porta> Slot: $ont[slot]  Porta: $porta </option>";
            }
          }
        }else{
          $sql_consulta_serial = "SELECT frame, slot, porta FROM pon WHERE pon_id = $olt";

          $executa_query = mysqli_query($conectar,$sql_consulta_serial) or die(mysqli_error($conectar));
          
          while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
          {
            for($porta = 0;$porta < $ont['porta'];$porta++)
            {
                //if(!in_array("$ont[frame]-$ont[slot]-$porta",$array_ctos))
                echo "<option value=$olt-$ont[frame]-$ont[slot]-$porta> Slot: $ont[slot]  Porta: $porta </option>";
            }
          }
        }
    ?>
  </select>
</div>

<div class="form-group">
  <label for="quantidadePortasAtendimento">Quantidade de Portas de Atendimento</label>
  <input id="quantidadePortasAtendimento" class="form-control" placeholder="Quantidade de Portas" name="portasAtendimento" type="number" 
    title="Somente Números" autofocus required>
</div>
<input type="hidden" name="tipoCTO" value=<?php echo $tipoCTO ?>>                           

<div class="form-group">
  disponibilizar: <input type='checkbox' name='cto_disponivel' value=1 checked onClick='return false'/>
</div>                                                    
<button class="btn btn-lg btn-success btn-block">Cadastrar</button>


  