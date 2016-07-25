<h1><?=__('WooYellowCube', 'wooyellowcube')?></h1>
<form action="" method="post">
<div class="wooyellowcube-overflow">
  <div class="wooyellowcube-inline">
  <h2><?=__('Personal informations', 'wooyellowcube')?></h2>

  <!-- Setter -->
  <p>
    <label for="setter"><?=__('Sender', 'wooyellowcube')?></label>
    <input type="text" name="setter" id="setter" value="<?=get_option('wooyellowcube_setter')?>" />
  </p>

  <!-- Receiver -->
  <p>
    <label for="receiver"><?=__('Receiver', 'wooyellowcube')?></label>
    <input type="text" name="receiver" id="receiver" value="<?=get_option('wooyellowcube_receiver')?>" />
  </p>

  <!-- DepositorNo -->
  <p>
    <label for="depositorNo"><?=__('DepositorNo', 'wooyellowcube')?></label>
    <input type="text" name="depositorNo" id="depositorNo" value="<?=get_option('wooyellowcube_depositorno')?>" />
  </p>

  <!-- PartnerNo -->
  <p>
    <label for="partnerNo"><?=__('PartnerNo', 'wooyellowcube')?></label>
    <input type="text" name="partnerNo" id="partnerNo" value="<?=get_option('wooyellowcube_partnerno')?>" />
  </p>

  <!-- Plant -->
  <p>
    <label for="plant"><?=__('Plant', 'wooyellowcube')?></label>
    <input type="text" name="plant" id="plant" value="<?=get_option('wooyellowcube_plant')?>" />
  </p>

</div>
<div class="wooyellowcube-inline">
  <h2><?=__('Technical informations', 'wooyellowcube')?></h2>
  <!-- SOAP Method -->
  <p>
    <label for="yellowcubeSOAPUrl"><?=__('SOAP url', 'wooyellowcube')?></label>
    <select name="yellowcubeSOAPUrl" id="yellowcubeSOAPUrl">
      <option value="1" <?=(get_option('wooyellowcube_yellowcubeSOAPUrl') == 1) ? 'selected="selected"' : '' ?>>https://service-test.swisspost.ch/apache/yellowcube-test/?wsdl</option>
      <option value="2" <?=(get_option('wooyellowcube_yellowcubeSOAPUrl') == 2) ? 'selected="selected"' : '' ?>>https://service-test.swisspost.ch/apache/yellowcube-int/?wsdl</option>
      <option value="3" <?=(get_option('wooyellowcube_yellowcubeSOAPUrl') == 3) ? 'selected="selected"' : '' ?>>https://service.swisspost.ch/apache/yellowcube/</option>
    </select>
  </p>

  <!-- Operating mode -->
  <p>
    <label for="operatingMode"><?=__('Operating mode', 'wooyellowcube')?></label>
    <select name="operatingMode" id="operatingMode">
      <option value="D" <?=(get_option('wooyellowcube_operatingMode') == 'D') ? 'selected="selected"' : '' ?>>Development</option>
      <option value="T" <?=(get_option('wooyellowcube_operatingMode') == 'T') ? 'selected="selected"' : '' ?>>Testing</option>
      <option value="P" <?=(get_option('wooyellowcube_operatingMode') == 'P') ? 'selected="selected"' : '' ?>>Production</option>
    </select>
  </p>

  <!-- Authentification -->
  <p>
    <label for="authentification"><?=__('Authentification', 'wooyellowcube')?></label>
    <select name="authentification" id="authentification" class="wooyellowcube_authentification">
      <option value="0" <?=(get_option('wooyellowcube_authentification') == 0) ? 'selected="selected"' : '' ?>>No</option>
      <option value="1" <?=(get_option('wooyellowcube_authentification') == 1) ? 'selected="selected"' : '' ?>>Yes</option>
    </select>
  </p>

  <p>
    <label for="authentificationFile"><?=__('Authentification file', 'wooyellowcube')?></label>
    <input type="text" name="authentificationFile" id="authentificationFile" value="<?=get_option('wooyellowcube_authentificationFile')?>" <?php if(get_option('wooyellowcube_authentification') == 0) echo 'disabled="disabled"'; ?> size="50" />
  </p>

  <h2><?=__('Lot management', 'wooyellowcube')?></h2>
  <p>
	  <label for="lotmanagement"><?=__('Lot management', 'wooyellowcube')?></label>
	  <select name="lotmanagement" id="lotmanagement">
		  <option value="0" <?php if(get_option('wooyellowcube_lotmanagement') == 0) echo 'selected="selected"'; ?>><?=__('Desactivated', 'wooyellowcube')?></option>
		  <option value="1" <?php if(get_option('wooyellowcube_lotmanagement') == 1) echo 'selected="selected"'; ?>><?=__('Activated', 'wooyellowcube')?></option>
	  </select>
  </p>

  <h2><?=__('Logs', 'wooyellowcube')?></h2>
  <p>
	  <label for="logs_delete"><?=__('Day before logs are removed from database', 'wooyellowcube')?></label>
	  <br />
	  <input type="text" name="logs_delete" id="logs_delete" value="<?=get_option('wooyellowcube_logs')?>" />
  </p>




  <p>
    <input type="submit" name="wooyellowcube-settings" value="<?=__('Save informations', 'wooyellowcube')?>" class="button" />
  </p>


</div>
</div>
</form>
