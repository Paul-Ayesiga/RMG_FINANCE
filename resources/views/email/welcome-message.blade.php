<!DOCTYPE html>
<html lang="en">
	<!--begin::Head-->
	<head><base href="../../"/>
		<title>Welcome to SofTech Micro Finance</title>
		<meta charset="utf-8" />
		<meta name="description" content="The most advanced Bootstrap 5 Admin Theme with 40 unique prebuilt layouts on Themeforest trusted by 100,000 beginners and professionals. Multi-demo, Dark Mode, RTL support and complete React, Angular, Vue, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js, Node.js, Flask, Symfony & Laravel versions. Grab your copy now and get life-time updates for free." />
		<meta name="keywords" content="metronic, bootstrap, bootstrap 5, angular, VueJs, React, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js, Node.js, Flask, Symfony & Laravel starter kits, admin themes, web design, figma, web development, free templates, free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button, bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content="Metronic - Bootstrap Admin Template, HTML, VueJS, React, Angular. Laravel, Asp.Net Core, Ruby on Rails, Spring Boot, Blazor, Django, Express.js, Node.js, Flask Admin Dashboard Theme & Template" />
		<meta property="og:url" content="https://keenthemes.com/metronic" />
		<meta property="og:site_name" content="Keenthemes | Metronic" />
		<link rel="canonical" href="https://preview.keenthemes.com/metronic8" />
		<link rel="shortcut icon" href="assets/media/logos/SMF.ico" />
		<!--begin::Fonts(mandatory for all pages)-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
		<link href="{{asset('email/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('email/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
		<script>// Frame-busting to prevent site from being loaded within a frame without permission (click-jacking) if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>
        <style>
            .nav{
                display: none;
            }
        </style>
    </head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="app-blank">
		<!--begin::Theme mode setup on page load-->
		<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
		<!--end::Theme mode setup on page load-->
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<!--begin::Wrapper-->
			<div class="d-flex flex-column flex-column-fluid">
				<!--begin::Header-->
				<div class="bg-body d-flex flex-column-auto justify-content-cenrer align-items-start gap-2 gap-lg-4 py-4 px-10 overflow-auto" id="kt_app_header_nav">
					<a href="#" class="flex-grow-1 mt-2">
						<i class="ki-duotone ki-arrow-left fs-2x text-gray-400">
							<span class="path1"></span>
							<span class="path2"></span>
						</i>
					</a>
					<a href="" class="btn btn-icon flex-column btn-text-gray-500 btn-icon-gray-300 btn-active-text-gray-700 btn-active-icon-gray-500 rounded-3 h-60px w-60px h-lg-90px w-lg-90px fw-semibold active bg-light border-2">
						<i class="ki-duotone ki-like fs-1 mb-2">
							<span class="path1"></span>
							<span class="path2"></span>
						</i>
						<span class="fs-8 fw-bold">Welcome
						<br />Message</span>
					</a>
                    <a class="flex-grow-1 text-end mt-2"><button id="close" style="border: none; border-radius:25%; width:33px; height:33px; padding:0px 0 0 0px">
						<i class="ki-duotone ki-cross-square fs-3x text-gray-400">
							<span class="path1"></span>
							<span class="path2"></span>
						</i>
					</button></a>

				</div>
				<!--end::Header-->
				<!--begin::Body-->
				<div class="scroll-y flex-column-fluid px-10 py-10" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header_nav" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true" style="background-color:#D5D9E2; --kt-scrollbar-color: #d9d0cc; --kt-scrollbar-hover-color: #d9d0cc">
					<!--begin::Email template-->
					<style>html,body { padding:0; margin:0; font-family: Inter, Helvetica, "sans-serif"; } a:hover { color: #009ef7; }</style>
					<div id="#kt_app_body_content" style="background-color:#D5D9E2; font-family:Arial,Helvetica,sans-serif; line-height: 1.5; min-height: 100%; font-weight: normal; font-size: 15px; color: #2F3044; margin:0; padding:0; width:100%;">
						<div style="background-color:#ffffff; padding: 45px 0 34px 0; border-radius: 24px; margin:40px auto; max-width: 600px;">
							<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" height="auto" style="border-collapse:collapse">
								<tbody>
									<tr>
										<td align="center" valign="center" style="text-align:center; padding-bottom: 10px">
											<!--begin:Email content-->
											<div style="text-align:center; margin:0 15px 34px 15px">
												<!--begin:Logo-->
												<div style="margin-bottom: 10px">
													<a href="#" rel="noopener" target="_blank">
                                                        <img width="200" height="150" alt="Logo" src="{{ asset('logos/logo.png') }}" />
													</a>
												</div>
												<!--end:Logo-->
												<!--begin:Media-->
												<div style="margin-bottom: 15px">
													<img alt="Logo" src="assets/media/email/icon-positive-vote-1.svg" />
												</div>
												<!--end:Media-->
												<!--begin:Text-->
												<div style="font-size: 14px; font-weight: 500; margin-bottom: 27px; font-family:Arial,Helvetica,sans-serif;">
                                                    @if(!empty(Auth::user()))
                                                        <p style="margin-bottom:9px; color:#181C32; font-size: 22px; font-weight:700">Hey {{Auth::user()->first_name}} {{Auth::user()->last_name}}, thanks for signing up!</p>
                                                        <p style="margin-bottom:2px; color:#7E8299">Thanks for signing up! Before getting started,</p>
                                                        <p style="margin-bottom:2px; color:#7E8299">
                                                            could you verify your email address by clicking on the link we just emailed to you?
                                                        </p>
                                                        <p style="margin-bottom:2px; color:#7E8299">
                                                            If you didn\'t receive the email, we will gladly send you another.
                                                        </p>
                                                    @else
										            <p style="margin-bottom:9px; color:#181C32; font-size: 22px; font-weight:700">Hey, thanks for signing up!</p>
                                                    <p style="margin-bottom:2px; color:#7E8299">Thanks for signing up! Before getting started,</p>
                                                    <p style="margin-bottom:2px; color:#7E8299">
                                                        could you verify your email address by clicking on the link we just emailed to you?
                                                    </p>
                                                    <p style="margin-bottom:2px; color:#7E8299">
                                                        If you didn\'t receive the email, we will gladly send you another.
                                                     </p>
                                                     @endif
												</div>
												<!--end:Text-->
												<!--begin:Action-->
                                                    @if (session('status') == 'verification-link-sent')
                                                        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                                                            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                                                        </div>
                                                    @endif
                                                <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
                                                    @csrf
                                                    <button type="submit" class="btn"  style="background-color:#50cd89; border-radius:6px;display:inline-block; padding:11px 19px; color: #FFFFFF; font-size: 14px; font-weight:500;"> {{ __('Resend Verification Email') }}</button>.
                                                </form>
											</div>
											<!--end:Email content-->
										</td>
									</tr>
									<tr style="display: flex; justify-content: center; margin:0 60px 35px 60px">
										<td align="start" valign="start" style="padding-bottom: 10px;">
											<p style="color:#181C32; font-size: 18px; font-weight: 600; margin-bottom:13px">What’s next?</p>
											<!--begin::Wrapper-->
											<div style="background: #F9F9F9; border-radius: 12px; padding:35px 30px">
												<!--begin::Item-->
												<div style="display:flex">
													<!--begin::Media-->
													<div style="display: flex; justify-content: center; align-items: center; width:40px; height:40px; margin-right:13px">
														<img alt="Logo" src="assets/media/email/icon-polygon.svg" />
														<span style="position: absolute; color:#50CD89; font-size: 16px; font-weight: 600;">1</span>
													</div>
													<!--end::Media-->
													<!--begin::Block-->
													<div>
														<!--begin::Content-->
														<div>
															<!--begin::Title-->
															<a href="#" style="color:#181C32; font-size: 14px; font-weight: 600;font-family:Arial,Helvetica,sans-serif">Complete your account</a>
															<!--end::Title-->
															<!--begin::Desc-->
															<p style="color:#5E6278; font-size: 13px; font-weight: 500; padding-top:3px; margin:0;font-family:Arial,Helvetica,sans-serif">Lots of people make mistakes while creating paragraphs Some writers just put whitespace in their text</p>
															<!--end::Desc-->
														</div>
														<!--end::Content-->
														<!--begin::Separator-->
														<div class="separator separator-dashed" style="margin:17px 0 15px 0"></div>
														<!--end::Separator-->
													</div>
													<!--end::Block-->
												</div>
												<!--end::Item-->
												<!--begin::Item-->
												<div style="display:flex">
													<!--begin::Media-->
													<div style="display: flex; justify-content: center; align-items: center; width:40px; height:40px; margin-right:13px">
														<img alt="Logo" src="assets/media/email/icon-polygon.svg" />
														<span style="position: absolute; color:#50CD89; font-size: 16px; font-weight: 600;">2</span>
													</div>
													<!--end::Media-->
													<!--begin::Block-->
													<div>
														<!--begin::Content-->
														<div>
															<!--begin::Title-->
															<a href="#" style="color:#181C32; font-size: 14px; font-weight: 600;font-family:Arial,Helvetica,sans-serif">Subscribe To Our NewsLetter</a>
															<!--end::Title-->
															<!--begin::Desc-->
															<p style="color:#5E6278; font-size: 13px; font-weight: 500; padding-top:3px; margin:0;font-family:Arial,Helvetica,sans-serif">Craft a headline that is both informative and will capture readers’ improtant attentions</p>
															<!--end::Desc-->
														</div>
														<!--end::Content-->
														<!--begin::Separator-->
														<div class="separator separator-dashed" style="margin:17px 0 15px 0"></div>
														<!--end::Separator-->
													</div>
													<!--end::Block-->
												</div>
												<!--end::Item-->
												<!--begin::Item-->
												<div style="display:flex">
													<!--begin::Media-->
													<div style="display: flex; justify-content: center; align-items: center; width:40px; height:40px; margin-right:13px">
														<img alt="Logo" src="assets/media/email/icon-polygon.svg" />
														<span style="position: absolute; color:#50CD89; font-size: 16px; font-weight: 600;">3</span>
													</div>
													<!--end::Media-->
													<!--begin::Block-->
													<div>
														<!--begin::Content-->
														<div>
															<!--begin::Title-->
															<a href="#" style="color:#181C32; font-size: 14px; font-weight: 600;font-family:Arial,Helvetica,sans-serif">Upgrade To Premium SMF package</a>
															<!--end::Title-->
															<!--begin::Desc-->
															<p style="color:#5E6278; font-size: 13px; font-weight: 500; padding-top:3px; margin:0;font-family:Arial,Helvetica,sans-serif">Use images to enhance your post, improve its flow, add humor, and explain complex topics</p>
															<!--end::Desc-->
														</div>
														<!--end::Content-->
													</div>
													<!--end::Block-->
												</div>
												<!--end::Item-->
											</div>
											<!--end::Wrapper-->
										</td>
									</tr>
									<tr>
										<td align="center" valign="center" style="font-size: 13px; text-align:center; padding: 0 10px 10px 10px; font-weight: 500; color: #A1A5B7; font-family:Arial,Helvetica,sans-serif">
											<p style="color:#181C32; font-size: 16px; font-weight: 600; margin-bottom:9px">It’s all about customers!</p>
											<p style="margin-bottom:2px">Call our customer care number: +812 6 3344 55 56</p>
											<p style="margin-bottom:4px">You may reach us at
											<a href="#" rel="noopener" target="_blank" style="font-weight: 600">devs.Softech.com</a>.</p>
											<p>We serve Mon-Fri, 9AM-18AM</p>
										</td>
									</tr>
									<tr>
										<td align="center" valign="center" style="text-align:center; padding-bottom: 20px;">
											<a href="#" style="margin-right:10px">
												<img alt="Logo" src="assets/media/email/icon-linkedin.svg" />
											</a>
											<a href="#" style="margin-right:10px">
												<img alt="Logo" src="assets/media/email/icon-dribbble.svg" />
											</a>
											<a href="#" style="margin-right:10px">
												<img alt="Logo" src="assets/media/email/icon-facebook.svg" />
											</a>
											<a href="#">
												<img alt="Logo" src="assets/media/email/icon-twitter.svg" />
											</a>
										</td>
									</tr>
									<tr>
										<td align="center" valign="center" style="font-size: 13px; padding:0 15px; text-align:center; font-weight: 500; color: #A1A5B7;font-family:Arial,Helvetica,sans-serif">
											<p>&copy; Copyright SofTech Micro Finance.
											<a href="https://keenthemes.com" rel="noopener" target="_blank" style="font-weight: 600;font-family:Arial,Helvetica,sans-serif">Unsubscribe</a>&nbsp; from newsletter.</p>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<!--end::Email template-->
				</div>
				<!--end::Body-->
			</div>
			<!--end::Wrapper-->
		</div>
		<!--end::Root-->
		<!--begin::Javascript-->
		<script>var hostUrl = "assets/";</script>
		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		<script src="{{ asset('email/plugins/global/plugins.bundle.js')}}"></script>
		<script src="{{ asset('email/js/scripts.bundle.js')}}"></script>
		<!--end::Global Javascript Bundle-->
		<!--end::Javascript-->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function(){
               $('#close').on('click',function(){
                    $('#kt_app_header_nav').style.display = "none";
               });
            });
        </script>
	</body>
	<!--end::Body-->
</html>
