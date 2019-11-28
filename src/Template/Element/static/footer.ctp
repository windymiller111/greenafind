 <?php use Cake\Routing\Router; ?>
 <footer class="footer-section">
            <div class="footer-links-frame">
                <div class="container">
                    <div class="row">
                        <section class="col-sm-4 newsletter-col">
                            <div class="content-frame">
                                <!--<h4 class="title-text">Newsletter</h4>-->
                                <p class="para">
                                    Insights await in your company's data. Bring them into focus with Greenafind
                                </p>
    
                                
                                <div class="newsletter-form">
                                    <!--<div class="form-frame">-->
                                        <!--<input type="text" name="email" id='emaiId' placeholder="enter your email" class="form-control" />-->

                                        <!--<button id = 'newsleter' class="btn-green btn-submit">&gt;</button>-->
                                         <!--<span class="error myResultDiv" id="invalid_email"></span>-->
                                    <!--</div>-->
                                </div><!-- =newsletter-form End//= -->

                            </div>
                        </section><!-- =newsletter Col end//= -->

                        <section class="col-sm-4 col-xs-6 quick-links-col">
                            <ul class="links-list">
                                <li class="title">
                                    <h4 class="title-text">Quick Links</h4>
                                </li>
                                <li>
                                    <a href="#how-it-works" >How It Works</a>
                                </li>
                                <!--<li>
                                    <a href="#">Privacy Policy</a>
                                </li>-->
                                <li>
                                    <a href="<?php echo Router::url('/terms-of-services'); ?>">Terms &amp; Condition</a>
                                   
                                </li>
                            </ul>
                        </section><!-- =Quick Links Col End//= -->

                        <section class="col-sm-4 col-xs-6 social-links-col">
                            <ul class="links-list">
                                <li class="title">
                                    <h4 class="title-text">Social Links</h4>
                                </li>
                                <li>
                                    <a href="#" class="icon icon-twitter"><i class="fab fa-twitter-square"></i></a>
                                    <a href="https://www.linkedin.com/company/greenafind" class="icon icon-linkedin"><i class="fab fa-linkedin"></i></a>
                                    <a href="https://www.facebook.com/GreenaFind-1244052089082578/" class="icon icon-fb"><i class="fab fa-facebook-square"></i></a>
                                </li>                                
                            </ul>
                        </section><!-- =Quick Links Col End//= -->
                    </div><!-- =Row End//= -->    
                </div>
            </div><!-- =footer links frame end//= -->

            <div class="btm-footer">
                <div class="container">
                    <section class="row">
                        <div class="col-sm-3 col-xs-6 logo-col">
                            <a href=<?php echo base_url; ?> class="logo"></a>
                        </div><!-- =Logo Col End//= -->
                        <div class="col-sm-9 col-xs-6 copyright-col">
                            <label class="text">&copy;2019</label>
                        </div><!-- =copyright Col End//= -->
                    </section><!-- =row End//= -->
                </div>
            </div><!-- =Btm Footer End//= -->
        </footer><!-- =Footer Section End//= -->