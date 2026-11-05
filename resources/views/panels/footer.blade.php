<footer class="footer footer-light @if(isset($configData['footerType'])){{$configData['footerClass']}}@endif">
  <p class="mb-0">&copy; {{ date('Y') }} AutoPower · Warranty Claim System</p>
  @if($configData['isScrollTop'] === true)
  <button class="btn btn-primary btn-icon scroll-top" type="button" style="display: inline-block;">
  <i class="bx bx-up-arrow-alt"></i>
  </button>
  @endif
</footer>

