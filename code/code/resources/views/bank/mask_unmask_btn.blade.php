<style>
 @media print{
  .mask_unmask_div_btn , .maskingfield{
    display:none !important;
  }
  .unmaskingfield{
    display:unset !important;
  }
  .uploaded-img-ovd{
    filter:blur(0px) !important;
  }
 }
</style>
<div class="d-flex justify-content-end mb-3 position-relative mask_unmask_div_btn">
  <div class="position-absolute" style="right: -17px; top: -66px; z-index: 3;">
    <button class="btn btn-sm btn-danger" style="display: none;" id="maskfields">Mask</button>
    <button class="btn btn-sm btn-success" id="unmaskfields">Unmask</button>
  </div>
</div>