<select class="form-control selectpicker" name=ctoSelect data-show-subtext="true" data-size=5 data-live-search="true" >
  <?php
    $sql_caixa_atendimento = "SELECT DISTINCT caixa_atendimento, disponivel, tipoCTO FROM ctos 
      where pon_id_fk = $olt order by caixa_atendimento";

    $executa_sql_caixa_atendimento = mysqli_query($conectar,$sql_caixa_atendimento);
    $celulas_caixa = array();
    
    while ($caixa_atendimento = mysqli_fetch_array($executa_sql_caixa_atendimento, MYSQLI_BOTH))
    {
      
      if($caixa_atendimento['tipoCTO'] == 'associada'){
        $explode_celula_caixa = explode('.',$caixa_atendimento['caixa_atendimento']);
        $caixa_celula_associada = "$explode_celula_caixa[0].B";
        array_push($celulas_caixa,"$explode_celula_caixa[0]B");
      }
      else{
        $explode_celula_caixa = explode('.',$caixa_atendimento['caixa_atendimento']);
        $caixa_celula = $explode_celula_caixa[0];
        array_push($celulas_caixa,$explode_celula_caixa[0]);
      }
      
      $unico = array_unique($celulas_caixa);
      
      if($_SESSION['nome_usuario'] != 'Charles Pereira' || $_SESSION['nome_usuario'] != 'Administrador') {
        $quantidade_celulas = sizeOf($unico);
        
        if($quantidade_celulas != $quantidade_celulas_atual){
          echo "$caixa_atendimento[caixa_atendimento]";
          if($caixa_atendimento['tipoCTO'] != 'associada')
            echo "<option name='cto' value=$caixa_atendimento[caixa_atendimento]>
              $caixa_celula
            </option>";
          else
            echo "<option name='cto' value=$caixa_atendimento[caixa_atendimento]>
              $caixa_celula_associada
            </option>";
          $quantidade_celulas_atual = sizeOf($unico);
        }
      }else{
        echo "<option>EM MANUTENCAO!, VOLTE LOGO MAIS!</option>";
        break;
      }
    }
  ?>
</select>

<div class="form-group">
  <label for='nCtos'>Quantidade de CTOs</label>
  <input class="" placeholder="No. CTOs" id="nCtos" name="nCtos" type="number" pattern="[0-9]" min=1 max=8 title="Digite a quantidade de CTOs que irão ser criadas" onblur="calcular();" autofocus required>
</div>

<div class="form-group">
  <label>PON</label> 
  <select class="form-control" name="pon">
    <?php
      $sql_check = "SELECT DISTINCT * FROM ctos WHERE pon_id_fk = $olt";
      $executa_check = mysqli_query($conectar,$sql_check);
      if(mysqli_num_rows($executa_check) > 0) //checa se ja existe CTO cadastrada na pon
      {
        $sql_consulta_serial = "SELECT DISTINCT olt.frame,olt.slot,olt.porta FROM pon olt 
          INNER JOIN ctos cto ON cto.pon_id_fk = $olt
          WHERE olt.pon_id = $olt";//"SELECT frame,slot,porta FROM pon WHERE pon_id = $olt";
        
        $array_ctos = array();

        $executa_query = mysqli_query($conectar,$sql_consulta_serial) or die(mysqli_error($conectar));

        $sql_get_fsp = "SELECT DISTINCT caixa_atendimento,frame_slot_pon FROM `ctos` 
          WHERE pon_id_fk = $olt" ;
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
                echo "<option value=$olt-$ont[frame]-$ont[slot]-$porta>  Slot: $ont[slot]  Porta: $porta </option>";
          }
        }
      }else{
        $sql_consulta_serial = "SELECT frame, slot, porta FROM pon WHERE pon_id = $olt";

        $executa_query = mysqli_query($conectar,$sql_consulta_serial) or die(mysqli_error($conectar));
        
        while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
        {
          for($porta = 0;$porta < $ont['porta'];$porta++)
          {
            echo "<option value=$olt-$ont[frame]-$ont[slot]-$porta> Slot: $ont[slot]  Porta: $porta </option>";
          }
        }
      }
    ?>
  </select>
</div>

<div class="form-group">
    <label>Quantidade de Portas de Atendimento</label>                                                
    <select class="form-control" name="porta">
        echo "<option value=8>8</option>";
    </select>
</div>
<input type="hidden" name="tipoCTO" value=<?php echo $tipoCTO ?>>

<div class="form-group">
  disponibilizar: <input type='checkbox' name='cto_disponivel' value=1 />
</div>                         
<button class="btn btn-lg btn-success btn-block">Cadastrar</button>

