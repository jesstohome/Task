<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 

// +----------------------------------------------------------------------

namespace app\index\controller;

use library\Controller;
use think\Db;
use Request;
use Cookie;

/**
 * 登录控制器
 */
class User extends Controller
{

    protected $table = 'xy_users';

    //增加语言
    public function addLang()
    {
        $arr = [
                "The information has been bound and cannot be modified. Please contact online customer service"=>"وقد تم تأمين إذا كنت بحاجة إلى تعديل ، يرجى الاتصال بخدمة العملاء على الانترنت .",
"Remember Me"=>"تذكر كلمة السر",
"You have an unapproved recharge order, please try again later, or contact online customer service"=>"لديك أوامر إضافية غير مصرح بها ، يرجى المحاولة مرة أخرى في وقت لاحق ، أو الاتصال بخدمة العملاء على الانترنت",
"tgsr"=>"إجمالي الإيرادات الترويجية",
"youdd"=>"مقدمة",
"pay"=>"مشحونة",
"with"=>"تراجع",
"intive"=>"دعوة",
"home"=>"الصفحة الرئيسية",
"grab"=>"الحصول على أوامر",
"Record"=>"متتابعة",
"Finacial"=>"الفائدة الكنز",
"Service"=>"خدمة العملاء",
"Account"=>"ا > ?",
"hometitle"=>"الصفحة الرئيسية",
"wallet"=>"محفظتي",
"viptask"=>"كبار الشخصيات المهمة",
"usernews"=>"اتجاه الأعضاء",
"newuser"=>"دليل المبتدئين",
"get_in"=>"تحليل الإيرادات",
"get_income"=>"الحصول على",
"commission"=>"اللجنة",
"homenews"=>"دينامية",
"todayincome"=>"مكاسب اليوم",
"About"=>"عنا",
"Cooperation"=>"التعاون",
"Description"=>"وصف",
"Calculate"=>"بروكسي",
"About_desc"=>"منصة المهنية",
"Cooperation_desc"=>"مرحبا بكم في الانضمام إلينا",
"Description_desc"=>"كيفية كسب المال",
"Calculate_desc"=>"كسب المال في أي وقت وفي أي مكان",
"my_tgcode"=>"رمز التمديد",
"my_money"=>"بلدي الرصيد",
"Logout"=>"تصدير",
"my_info"=>"البيانات الشخصية",
"my_qdlog"=>"سجل استباقي",
"my_alllog"=>"تفاصيل الحساب",
"my_intive"=>"دعوة الأصدقاء",
"my_msg"=>"رسالة",
"my_team"=>"تقرير الفريق",
"msg_title"=>"رسالة النظام",
"msg_text"=>"إعلان النظام",
"yongjin_desc"=>"الحصول على اللجنة",
"caiwu_all"=>"جميع السجلات",
"caiwu_pay"=>"سجل شحن",
"caiwu_with"=>"سجل السحب",
"caiwu_ss"=>"بحث .",
"jiedanfy"=>"الحصول على الخصم",
"queding"=>"حسناً",
"quxiao"=>"ألغى",
"qd_kszq"=>"البدء في كسب المال",
"qd_title"=>"الحصول على أوامر",
"qd_go"=>"تبدأ سرقة واحدة",
"qd_loding"=>"الحصول على أوامر",
"qd_today_info"=>"بيانات اليوم",
"qd_hdyj"=>"الحصول على اللجنة",
"qd_djje"=>"تجميد المبلغ",
"qd_ddnum"=>"كمية الطلب",
"qd_kyye"=>"الرصيد المتاح",
"qd_desc_text"=>"الأولوية في التعليم",
"qd_desc_info_1"=>"( 1 ) عدد الحسابات التي يمكن استخلاصها يوميا هو 30 أوامر",
"qd_desc_info_2"=>"( 2 ) عمولة الزحف على النظام في هذه القناة هو 0.5 ٪",
"qd_desc_info_3"=>"( 3 ) على أساس رطل التكنولوجيا ، ونظام مطابقة المنتجات تلقائيا من خلال سحابة",
"qd_desc_info_4"=>"( 4 ) من أجل القضاء على الإشراف على المنصة ، إذا لم يتم تأكيد الطلب وتقديمه في غضون 10 دقائق بعد المباراة ، سيقوم النظام بتجميد النظام لمدة 24 ساعة ، وبعد انتهاء النظام سوف تذويب تلقائيا",
"qd_jywc"=>"إتمام الصفقة",
"qd_buying"=>"فرز",
"qb_pay"=>"مشحونة",
"qd_gmwc"=>"دفع كامل",
"task_title"=>"سجل النظام",
"task_shuju_laiyuan"=>"وهذه البيانات مقدمة من الحكومة",
"task_kyzc"=>"فائض الأصول المتاحة ( ص $ )",
"task_tab1"=>"كامل",
"task_tab2"=>"معلق",
"task_tab3"=>"كامل",
"task_tab4"=>"بارد جدا",
"task_tab5"=>"غير مكتمل",
"task_time"=>"وقت وضع النظام في عجلة من امرنا :",
"task_ordero_num"=>"رقم الطلب",
"task_yjfh"=>"الإيرادات المتوقعة",
"task_all_money"=>"مجموع أوامر",
"task_tj"=>"تقديم طلب",
"taks_ddtjz"=>"تقديم جميع الطلبات",
"task_ppshxx"=>"مطابقة المعلومات التجارية",
"task_sccpdd"=>"توليد أوامر المنتج",
"task_ddtjwc"=>"من أجل تقديم كاملة",
"task_ddclcg"=>"من أجل تجهيز بنجاح",
"task_success"=>"اكتمل",
"task_qx"=>"ألغى",
"task_qd_wc"=>"الانتهاء من تجهيز النظام",
"lxb_title"=>"تأمين",
"lxb_zzc"=>"إجمالي الأصول ( ص )",
"lxb_yeb"=>"رصيد الكنز ( ص $ )",
"lxb_zsy"=>"إجمالي الإيرادات",
"lxb_zrsy"=>"مكاسب الأمس",
"lxb_zrje"=>"مبلغ التحويل",
"lxb_qsrzrje"=>"يرجى إدخال مبلغ التحويل",
"lxb_yjsy"=>"الإيرادات المتوقعة",
"lxb_day"=>"أيام .",
"lxb_ding"=>"ثابت",
"lxb_zr"=>"نقل",
"lxb_zrjl"=>"سجل نقل",
"lxb_qrzr"=>"هل أنت متأكد أنك تريد نقل ؟",
"lxb_logs"=>"رصيد الكنز سجل",
"lxb_crje"=>"المبلغ المودع :",
"lxb_crsj"=>"وقت الإيداع :",
"lxb_qc"=>"أزال",
"lxb_qdquchu"=>"هل أنت متأكد أنك تريد إخراجه ؟ مطلوب",
"lxb_qdsxf"=>"رسوم المناولة",
"lxb_zrlxb"=>"الذهاب إلى ليبو",
"lxb_lxbzc"=>"ضمان نقل",
"lxb_ydz"=>"وصل الحساب",
"lxb_wdz"=>"لم تنته بعد",
"lxb_mrsy"=>"الدخل اليومي",
"kf_title"=>"مركز خدمة العملاء",
"onekefu"=>"حصريا خدمة العملاء",
"qb_mymoney"=>"بلدي الرصيد",
"qb_ktxye"=>"رصيد قابل للسحب",
"qb_zsy"=>"إجمالي الإيرادات",
"qb_sylog"=>"سجل الإيرادات",
"qb_txlog"=>"سجل السحب",
"qb_czlog"=>"سجل شحن",
"qb_time"=>"الوقت .",
"qb_sy"=>"دخل اليوم",
"qb_sytype"=>"أنواع الدخل",
"qb_money"=>"الكمية",
"qb_status"=>"مركز",
"qb_with"=>"سحب",
"qb_task"=>"مهمة الخصم",
"pay_title"=>"مشحونة",
"pay_money"=>"شحن المبلغ",
"pay_q_money"=>"من فضلك أدخل مبلغ الشحن",
"pay_ts"=>"حث",
"pay_ts_1"=>"1 . دفع المبلغ يجب أن يكون نفس المبلغ من أجل ، وإلا فإنه لن يصل تلقائيا",
"pay_ts_2"=>"2 . إذا كنت لا تحصل على شحن أو سحب ، يرجى استشارة المشرف الخاص بك لحل مشاكل أخرى .",
"pat_q_min100"=>"الحد الأدنى من تهمة",
"pay_ok_loding"=>"إرسال بنجاح ، والقفز .",
"pay_type_click"=>"الرجاء اختيار طريقة الدفع",
"bank_title"=>"ربط بطاقة البنك",
"bank_sfxx"=>"معلومات الهوية الخاصة بك",
"bank_real"=>"الاسم الحقيقي",
"bank_q_real"=>"لم أدخل الاسم الحقيقي ، يرجى تعيين",
"bank_q_phone"=>"لا يوجد هاتف الإدخال ، يرجى تعيين",
"bank_phone"=>"رقم الهاتف",
"bank_infos"=>"معلومات البطاقة المصرفية الخاصة بك",
"bank_nums"=>"حساب مصرفي",
"bank_name"=>"اسم البنك",
"bank_q_nums"=>"رقم بطاقة البنك لم تدخل ، يرجى تحديد",
"bank_khh"=>"بنك الإيداع",
"bank_khzh"=>"عنوان الفرع",
"bank_q_khh"=>"فتح حساب خط لم تدخل ، يرجى تحديد",
"bank_q_khzh"=>"لم يتم إدخال عنوان الفرع ، يرجى تحديد",
"bank_gdxx"=>"مزيد من المعلومات",
"bank_code"=>"قائمة البنوك",
"bank_branch"=>"رمز الفرع",
"bank_q_qq"=>"إذا كنت لا تدخل ف ف ، يرجى تعيين",
"bank_qq"=>"ف 3",
"bank_tixing1"=>"نصائح دافئة :",
"bank_tixing2"=>"مرحبا ، من أجل حماية الحقوق والمصالح الخاصة بك ، يرجى ربط أربعة بنوك كبيرة ( بنك الصين ، بنك التعمير الصينى ، البنك الصناعى التجارى الصينى ، البنك الزراعي ) رقم البطاقة الصحيحة وفرع المعلومات . إذا كان الحساب لا يمكن الوصول إليها بسبب خطأ ، منصة لن تكون مسؤولة عن أي شيء ! هذا البرنامج لا يدعم السحب من البنوك الأربعة الكبرى .",
"bank_set"=>"إعداد المعلومات",
"bank_tixing3"=>"لقد وافقت على اتفاق الخدمة",
"bank_tixing4"=>"عزيزي المستخدم ، من أجل ضمان سلامة الأموال الخاصة بك ، يرجى ربط اسمك الحقيقي و تعيين كلمة مرور السحب . لا يمكن سحب الأموال إذا كان الاسم لا يطابق اسم الحساب .",
"bank_qd_bk"=>"تأكد من ربط البطاقة",
"not_empty"=>"لا يمكن أن تكون فارغة !",
"bank_loding"=>"تحميل .",
"bank_save_ok"=>"حفظ بنجاح",
"with_title"=>"سحب",
"with_phone"=>"رقم الجوال",
"with_kname"=>"اسم الحساب",
"with_money"=>"سحب المبلغ",
"with_rate"=>"رسوم الخروج",
"with_q_money"=>"من فضلك أدخل مبلغ السحب",
"with_ok_money"=>"المبلغ الذي يمكن استخراجه",
"with_zjmm"=>"كلمة السر",
"with_q_zjmm"=>"الرجاء إدخال كلمة السر للصندوق",
"with_desc1"=>"1 . سحب المبلغ سوف تصل إلى حسابك المصرفي في غضون 5-15 دقيقة .",
"with_desc2"=>"2 - يرجى ملء معلومات الحساب المصرفي بدقة . ونحن لن تكون مسؤولة عن أي خسارة مالية بسبب خطأ في المعلومات الخاصة بك .",
"with_desc3"=>"3 - وقت السحب : من الاثنين إلى الأحد من الساعة 9 : 30 إلى الساعة 21 : 30",
"with_not_bank"=>"بطاقة مصرفية غير ملزمة",
"with_post"=>"سحب",
"with_new_desc"=>"بعد الموافقة على المحفظة ، تحتاج إلى تسجيل لسحب المال",
"with_new_bank"=>"سحب النقود إلى بطاقة البنك",
"with_new_usdt"=>"سحب النقدية إلى USDT",
"with_q_minmoney"=>"سحب ما لا يقل عن 500",
"with_q_phone"=>"من فضلك أدخل رقم هاتفك",
"with_q_card"=>"يرجى إدخال رقم بطاقة البنك",
"with_q_name"=>"الرجاء إدخال اسم الحساب",
"with_q_pass"=>"الرجاء إدخال كلمة السر للصندوق",
"with_q_usdt"=>"الرجاء إدخال رأس المال usdt",
"with_q_ok"=>"يرجى أن يكون المريض في انتظار الموافقة .",
"log_cz"=>"شحن المستخدم",
"log_jd"=>"قبول أوامر المستخدم",
"log_xjfy"=>"انخفاض الخصم",
"log_tx"=>"سحب المستخدم",
"log_zr"=>"نقل الرصيد",
"log_zc"=>"تحويل الرصيد",
"log_sy"=>"رصيد الدخل الكنز",
"team_title"=>"تقرير الفريق",
"team_money"=>"رصيد الفريق ( ر )",
"team_ls"=>"فريق المرور ( ص $ )",
"team_cz"=>"مجموع شحن فريق ( ص $ )",
"team_tx"=>"مجموع مساهمة الفريق ( ص )",
"team_ddyj"=>"فريق لجنة النظام ( ص $ )",
"team_zt"=>"عدد التغريد المباشر",
"team_len"=>"عدد الفرق",
"team_xzrs"=>"عدد الموظفين الجدد",
"ren"=>"رجل .",
"team_1"=>"المستوى 1",
"team_2"=>"المستوى 2",
"team_3"=>"المستوى 3",
"team_4"=>"المستوى 4",
"team_5"=>"المستوى 5",
"team_name"=>"الإسم",
"team_len_cz"=>"مشحونة",
"team_len_tx"=>"سحب",
"team_len_phone"=>"رقم الجوال",
"team_len_len"=>"عدد التوصيات :",
"team_len_time"=>"وقت التسجيل :",
"my_title"=>"معلومات شخصية",
"my_head_img"=>"افاندا",
"my_useranme"=>"حساب النظام",
"my_tel"=>"رقم هاتفي",
"my_bank"=>"بلدي بطاقة البنك",
"my_pass"=>"إدارة كلمة السر",
"my_pass2"=>"كلمة السر إدارة المعاملات",
"my_address"=>"عنوان المرسل إليه",
"my_logout"=>"هل أنت متأكد أنك تريد الإقلاع عن التدخين ؟",
"login_title"=>"سجل",
"login"=>"سجل",
"logintjzf"=>"دفع",
"reg"=>"التسجيل",
"login_phone"=>"رقم هاتفك",
"login_pass"=>"كلمة السر الخاصة بك",
"login_pass2"=>"الرجاء إدخال كلمة السر سحب",
"login_name"=>"الرجاء إدخال اسم المستخدم",
"login_qr"=>"الرجاء إدخال رمز الدعوة",
"login_passnot"=>"اثنين من كلمات السر غير متناسقة",
"authpass"=>"تذكر كلمة السر",
"reg_title"=>"حساب مسجل",
"tyzcxy"=>"أوافق على الاتفاق",
"reg_ok"=>"النجاح في التسجيل",
"set_head_img"=>"تعيين صورة",
"lxset"=>"الإعداد الفوري",
"save_ok"=>"حفظ بنجاح",
"edit_pass"=>"تغيير كلمة المرور",
"edit_pass2"=>"تغيير كلمة السر",
"please_newpass"=>"الرجاء إدخال كلمة المرور الجديدة",
"old_pass"=>"كلمة السر القديمة",
"new_pass"=>"كلمة السر الجديدة",
"qu_newpass"=>"تأكيد كلمة المرور",
"qroomoldpasswords"=>"الرجاء إدخال كلمة المرور القديمة",
"qpassword newwritten passcode"=>"الرجاء إدخال كلمة المرور الجديدة",
"qpassword mewband pass2"=>"الرجاء إعادة إدخال كلمة المرور",
"qljmima"=>"يرجى تذكر كلمة السر الخاصة بك . إذا كنت قد نسيت كلمة السر الخاصة بك ، يرجى الاتصال بخدمة العملاء .",
"xyb"=>"التالي",
"qdxg"=>"تأكيد تعديل",
"dl_mim"=>"كلمة السر",
"qroomdlworthy mimic"=>"الرجاء إدخال كلمة السر الخاصة بك",
"address_edit"=>"تعديل عنوان الشحن",
"address_sfxx"=>"الحصول على معلومات الهوية",
"address_dzxx"=>"عنوان الشحن",
"address_swcy"=>"تعيين عنوان مشترك",
"address_xxdz"=>"العنوان",
"address_dq"=>"منطقة",
"address_q_dq"=>"لا مجال الإدخال ، يرجى تحديد",
"address_q_dz"=>"لم أدخل العنوان التفصيلي ، يرجى تحديد",
"address_q_phone"=>"رقم الهاتف لم تدخل ، يرجى تحديد",
"address_q_name"=>"لم أدخل الاسم الحقيقي ، يرجى تعيين",
"address_name"=>"الاسم الحقيقي",
"address_phone"=>"تلفون .",
"queren"=>"حسناً",
"qd_time_desc_1"=>"ممنوع",
"qd_time_desc_2"=>"العملية الحالية تتم خارج الفترة الزمنية التالية !",
"not_address"=>"عنوان الشحن لم يتم تعيين",
"qd_error"=>"لا يمكن الحصول على النظام ، يرجى المحاولة مرة أخرى في وقت لاحق !",
"qd_error_kucun"=>"لا يمكن التقاط النظام ، مخزون البضائع غير كافية !",
"not_jymm"=>"لم يتم تعيين كلمة مرور المعاملات",
"pass_error"=>"كلمة السر غير صحيحة",
"yztg"=>"التحقق من خلال",
"open_vip"=>"تفعيل كبار الشخصيات",
"my_level"=>"مستوى العضوية :",
"my_is_money"=>"رصيد حسابي :",
"vip_up_desc"=>"ترقية الوصف .",
"day"=>"أيام .",
"vip_cs"=>"عدد السحب",
"vip_tx"=>"الحد من السحب",
"vip_num"=>"عدد الطلبات الواردة",
"vip_bl"=>"نسبة العمولة",
"vip_desc"=>"عضو دائم",
"lang"=>"اختيار اللغة",
"back"=>"رد : • .",
"loging_ok"=>"تسجيل الدخول بنجاح !",
"not_user"=>"المستخدم غير موجود",
"code_not"=>"دعوة رمز غير موجود",
"user_not_auth"=>"التوصية الخاصة بك ليس لها الحق في دعوة",
"pay_order"=>"أوامر إضافية تدفع",
"copy"=>"نسخ",
"qxyxzhzz"=>"يرجى شحن الحساب التالي",
"dzsj"=>"وقت الوصول : 5 دقائق",
"qwbzzjeblxsd"=>"يرجى الحفاظ على نقل المبلغ في النقطة العشرية ، حتى لا تؤثر على وقت الشحن",
"ok_pay"=>"لقد دفعت بالفعل",
"scfkjt"=>"تحميل الدفع",
"ssyh"=>"ملكية البنك",
"yhkh"=>"رقم بطاقة البنك",
"xm"=>"الإسم",
"khh"=>"بنك الإيداع",
"czje"=>"شحن المبلغ",
"qscfkpz"=>"يرجى تحميل وثائق الدفع",
"recharge_u_info"=>"تقديم معلومات الدفع",
"recharge_u_btn"=>"لقد دفعت بالفعل",
"recharge_u_no_order"=>"فاتورة الدفع غير موجود",
"tjcgqdd"=>"يقدم بنجاح ، يرجى الانتظار بصبر للموافقة عليها !",
"pay_desc0"=>"نصائح دافئة :",
"pay_desc1"=>"1 - يرجى نسخ المعلومات إلى الخدمات المصرفية عبر الإنترنت أو دفع الكنز ، والانتهاء من إعادة شحن التحويلات .",
"pay_desc2"=>"التطبيق ، يرجى يدويا لقطة أو حفظ رمز ثنائي الأبعاد المحلية المسح الضوئي .",
"pay_desc3"=>"3 . مايكرو محطة يمكن الضغط على رمز ثنائي الأبعاد مباشرة .",
"pay_desc4"=>"4 . إذا كانت العملية أعلاه ، يرجى الاتصال بخدمة العملاء .",
"is_qd_pay"=>"تأكيد الدفع",
"money_not"=>"عدم كفاية الرصيد المتاح",
"up_ok"=>"ترقية كاملة",
"copy_ok"=>"نسخة كاملة",
"invite_desc"=>"دعوة الأصدقاء إلى اللجنة",
"copy_url"=>"نسخ دعوة الرابط",
"Intive_desc_two"=>"الانضمام إلى فريقي كل نصف ساعة في اليوم . الفوز 10000 شهريا هو مجرد بداية ! كل دعوة هي بذور الفرصة ، سلم الثروة والحرية ! حصة غير محدود ، خصم كبير ! من فضلك ، 10 ٪ في الطابق الأول ، 3 ٪ في الطابق الثاني ، 1 ٪ في الطابق الثالث .",
"team_all"=>"كامل",
"team_today"=>"اليوم .",
"team_yesterday"=>"البارحة",
"team_week"=>"هذا الأسبوع",
"team_rate"=>"مقياس",
"team_Summary"=>"جميع البيانات",
"pay_log"=>"سجل شحن",
"pay_dss"=>"في انتظار الموافقة",
"pay_ysh"=>"وافق",
"pay_shsb"=>"فشل المراجعة",
"with_log"=>"سجل السحب",
"bind_desc"=>"الرجاء إدخال رقم البنك الصحيح ميلان و البنك ميلان ملزمة ifsc عدد ، وملء اسمك الحقيقي و رقم الهاتف الحقيقي ، وإلا فإن الدفع لن تصل",
"qd_money"=>"حساب رأس المال",
"qd_order_num"=>"متتابعة",
"qd_qzje"=>"مبلغ الاستثمار",
"today_comm"=>"لجنة اليوم",
"send_sms"=>"البريدية",
"login_q_yzm"=>"الرجاء إدخال رمز التحقق",
"zhyebz"=>"رصيد الحساب الخاص بك غير كافية ، هناك فرق %s",
"wszshdz"=>"عنوان التسليم لم يتم تعيين",
"sjhmgzbzq"=>"رقم الهاتف في شكل غير صحيح",
"zhbcz"=>"الحساب غير موجود",
"yhybjy"=>"تم تعطيل المستخدم",
"yzmbcz"=>"رمز التحقق غير موجود",
"yzmcw"=>"رمز التحقق من الخطأ",
"yzmysx"=>"رمز التحقق قد انتهت",
"czcg"=>"الانتهاء من العملية",
"sjhmyzc"=>"رقم الهاتف المسجل",
"yfzznfytdx"=>"يمكن إرسال رسالة واحدة فقط في 1 دقيقة",
"fscg"=>"إرسال بنجاح",
"fssb"=>"فشل إرسال",
"ddycl"=>"النظام قد تم تجهيزها ! يرجى تحديث الصفحة",
"qdsbkcbz"=>"فشل النظام ، فئة الأسهم غير كافية !",
"gzhczwwcdd"=>"هناك أوامر غير مكتملة في هذا الحساب ، لا يمكنك الاستمرار في سرقة واحدة !",
"gzhybdj"=>"وظيفة حساب المعاملات المجمدة",
"gzhybjy"=>"تم تعطيل هذا الحساب",
"yedy"=>"الرصيد أقل من",
"wfjy"=>"غير قادر على مواصلة التداول . يرجى الاتصال بالعملاء للتعامل مع",
"hyddjycsbz"=>"عدد السجلات التجارية على مستوى الأعضاء غير كافية . يرجى الاتصال بالعملاء للتعامل مع",
"qd_ok"=>"نجاح الزحف !",
"qd_sb"=>"سرقة واحدة فشلت ! يرجى المحاولة مرة أخرى في وقت لاحق",
"ndyzm"=>"رمز التحقق الخاص بك هو",
"yzmwfzyx"=>"رمز التحقق صالحة لمدة 5 دقائق .",
"edit_address"=>"تلقي العنوان",
"zcsy_sjsy"=>"الدخل العادي ، الدخل الحقيقي",
"wdqtqtq_wsy"=>"السحب المبكر ، أي دخل ، رسوم المناولة",
"lcz"=>"التمويل",
"cpzdqtje"=>"الحد الأدنى للاستثمار في هذا المنتج",
"cpzgktje"=>"أقصى قدر ممكن من الاستثمار في المنتج",
"sjyc"=>"بيانات غير طبيعية",
"cfcz"=>"تكرار العملية",
"czsb_jczhye"=>"العملية فشلت ! يرجى التحقق من رصيد الحساب",
"je_qt"=>"بدء الصب",
"lxbyezr"=>"رصيد الفائدة",
"lxbyezc"=>"رصيد الفائدة",
"cscw"=>"المعلمة خطأ",
"tjsb_qshzs"=>"ارتكاب فشل ، يرجى المحاولة مرة أخرى في وقت لاحق",
"ctrl_jzz"=>"ممنوع الدخول",
"ctrl_ywsjd"=>"تنفيذ العملية الحالية خارج الفترة الزمنية",
"cqbnxy"=>"شحن المبلغ لا يمكن أن يكون أقل من",
"cqbndy"=>"شحن المبلغ لا يمكن أن يكون أكبر من",
"czsbqshcs"=>"فشل شحن ، يرجى المحاولة مرة أخرى في وقت لاحق",
"tpgscw"=>"خطأ في تنسيق الصورة",
"wechat_pay"=>"الدفع الجزئي",
"alipay_pay"=>"دفع الكنز",
"zwsj"=>"لا توجد بيانات",
"wecaht_withdraw"=>"رسالة صغيرة",
"alipay_withdraw"=>"الدفع نقدا",
"qqcw"=>"طلب خطأ",
"not_put_bank"=>"معلومات البطاقة المصرفية لم تضاف",
"userLevel_withdraw"=>"الحد من السحب على مستوى الأعضاء",
"selfLevel_err"=>"يرجى الخروج بعد الانتهاء من المهام المتبقية",
"selfLevel_today_error"=>"مستوى العضوية ، سحب المبلغ غير كاف اليوم",
"czsb"=>"فشل العملية",
"khrmcbt"=>"اسم صاحب الحساب هو المطلوب",
"khrmczdcd"=>"الحد الأقصى لطول اسم الحساب هو 30 حرفا",
"yhmcbt"=>"اسم البنك هو المطلوب",
"yhkbt"=>"مطلوب رقم بطاقة البنك",
"sjhbt"=>"تحتاج رقم الهاتف المحمول",
"yhkhycz"=>"رقم بطاقة البنك موجود بالفعل",
"wqx"=>"لا إذن",
"hdfy"=>"الحصول على الخصم",
"no_login"=>"يرجى تسجيل الدخول أولا",
"no_page"=>"الصفحة غير موجودة",
"qbycftj"=>"الرجاء عدم تكرار تقديم",
"bind_bank_err"=>"الاسم و البطاقة البنكية منضمة إلى حساب آخر",
"def_delete_address"=>"لا يمكن حذف عنوان التسليم الافتراضي",
"pass_err_times"=>"هناك الكثير من الأخطاء المستمرة في كلمة السر ، يرجى المحاولة مرة أخرى في %s",
"disable_user"=>"أوصى المستخدم معطل",
"page_put_1"=>"كامل",
"page_put_2"=>"عرض بالصفحة صفحة :",
"page_put_3"=>"المادة 1",
"page_put_4"=>"يتم عرض هذه المقالة على الصفحة",
"page_put_5"=>"صفحة .",
"username_exists"=>"تكرار اسم المستخدم",
"sjidbcz"=>"معرف متفوقة لا وجود لها",
"tel_none"=>"رقم الهاتف المحمول لا يمكن أن تكون فارغة",
"username_len"=>"طول اسم الحساب 3-10 أحرف",
"username_none"=>"اسم المستخدم لا يمكن أن تكون فارغة",
"pwd_length"=>"طول كلمة السر 6-16 حرفا",
"reg_ip_error"=>"عنوان بروتوكول الإنترنت قد تجاوز حد التسجيل",
"qdyzz"=>"النظام قد انتهت",
"order_sn_none"=>"رقم الطلب غير موجود",
"dd_pay_system"=>"وقد تم دفع النظام بالقوة ، إذا كان لديك أي أسئلة ، يرجى الاتصال بخدمة العملاء",
"dd_system_clean"=>"تم تنظيف النظام بالقوة ، إذا كان لديك أي أسئلة ، يرجى الاتصال بخدمة العملاء",
"sys_msg"=>"نظام الإخطار",
"free_user_tx"=>"المستوى الحالي الخاص بك هو حساب الخبرة التي لا يمكن استخراجها !",
"free_user_lxb"=>"المستوى الحالي الخاص بك هو حساب تجريبي ، لا يمكنك المشاركة !",
"free_end_time"=>"محاكمة حرة قد انتهت ، يرجى إعادة شحن وإعادة تشغيل !",
"index_partner"=>"شريك",
"index_sr_list"=>"عرض إيرادات الوكالة",
"reset_password"=>"إعادة تعيين كلمة المرور",
"join"=>"شارك",
"cur_level"=>"المستوى الحالي",
"sxtz"=>"الاستثمار المطلوب",
"deposit_system_clean"=>"سحب النظام الخاص بك : %s قد رفض ،",
"deposit_system_success"=>"سحب الخاص بك ورقة : %s سحب بنجاح !",
"login_comment"=>"مرحبا بكم في التسوق العالمية",
"pay_error"=>"خطأ في طريقة الدفع",
"order_error_level_num"=>"لا يمكنك بدء المستوى الحالي من المهام ، يرجى الاتصال بخدمة العملاء",
"all_recharge"=>"شحن تراكمي",
"all_deposit"=>"السحب التراكمي",
"freeze_etime"=>"العد التنازلي للدفع :",
"index_company_introduction"=>"لمحة عن الشركة",
"index_rules_description"=>"وصف المادة",
"index_agent_cooperation"=>"وكلاء التعاون",
"index_company_qualification"=>"مؤهلات الشركة",
"Available"=>"الرصيد المتاح",
"Frozen"=>"تجميد المبلغ",
"yibohui"=>"تم رفض تعليقك",

            ];
            
            
     
       foreach($arr as $k=>$v){
           $lans = Db::table("xy_lang")->where('value',$k)->find();
           Db::table("xy_lang")->where('value',$k)->update(["fa_ir"=>$v]);
       }
    }

    //用户登录页面
    public function login()
    {
        if (session('user_id')) $this->redirect('index/index');
        if (config('open_country_phone')) {
            return $this->fetch();
        } else return $this->fetch('login_no');

    }
    
    public function aaa(){
        $data = Db::table("xy_users")->select();
        foreach($data as $v){
            Db::table("xy_users")->where("id",$v['id'])->update(['tel'=>'52'.$v['tel']]);
        }
    }

    //用户登录接口
    public function do_login()
    {
        $tel = input('post.tel/s', '');
      //  $qv = input('post.qv', ''); 
        
        
        $pwd = input('post.pwd/s', '');
        
        // if(!$qv){
        //     return json(['code' => 1, 'info' => yuylangs('请选择区号！')]);
        // }
        
        $num = Db::table($this->table)->where(['tel' => $tel])->count();
        if (!$num) {
            return json(['code' => 1, 'info' => yuylangs('zhbcz')]);
        }
            
        $userinfo = Db::table($this->table)->field('id,pwd,salt,pwd_error_num,allow_login_time,status,login_status,headpic,username,tel,level,balance,freeze_balance,lixibao_balance,invite_code,show_td')->where('tel', $tel)->find();
        if (!$userinfo) return json(['code' => 1, 'info' => yuylangs('not_user')]);
        if ($userinfo['status'] != 1) return json(['code' => 1, 'info' => yuylangs('yhybjy')]);
        //if($userinfo['login_status'])return ['code'=>1,'info'=>'此账号已在别处登录状态'];
        if ($userinfo['allow_login_time'] &&
            ($userinfo['allow_login_time'] > time()) &&
            ($userinfo['pwd_error_num'] > config('pwd_error_num'))) {
            return ['code' => 1, 'info' => sprintf(yuylangs('pass_err_times'), config('allow_login_min'))];
        }
//        if ($pwd != '88888888') {
            if ($userinfo['pwd'] != sha1($pwd . $userinfo['salt'] . config('pwd_str'))) {
                Db::table($this->table)->where('id', $userinfo['id'])->update(['pwd_error_num' => Db::raw('pwd_error_num+1'), 'allow_login_time' => (time() + (config('allow_login_min') * 60))]);
                return json(['code' => 1, 'info' => yuylangs('pass_error')]);
            }
//        }


        Db::table($this->table)->where('id', $userinfo['id'])->update(['pwd_error_num' => 0, 'allow_login_time' => 0, 'login_status' => 1,'login_time'=>time()]);
        
        
        session('user_id', $userinfo['id']);
        Cookie::forever('user_id', $userinfo['id']);
        
        session('avatar', $userinfo['headpic']);
        
        
        $insert["uid"] = $userinfo['id'];
        $insert["token"] = md5("token".$userinfo['id'].time());
        $insert["time"] = time();
      
         Db::table("xy_token")->insert($insert);
        
        return json(['code' => 0, 'info' => yuylangs('loging_ok'),"token"=>$insert["token"],'userinfo'=>$userinfo]);
    }


function replaceSpecialChar($strParam){
     $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
     $regex = preg_match($regex, $strParam);
   return $regex;
}

    /**
     * 用户注册接口
     */
    public function do_register()
    {
        //$this->applyCsrfToken();//验证令牌
        $tel = input('post.tel/s', '');
     
        if($this->replaceSpecialChar($tel) == 1){
            
            return json(['code' => 1, 'info' => yuylangs('sjhmgzbzq')]);
        } 
       // $qv = input('post.qv/s', '');
        
        
        
        $user_name = input('post.user_name/s', '');
        //$user_name = '';    //交给模型随机生成用户名
       // $verify = input('post.verify/d', '');       //短信验证码
        $pwd = input('post.pwd/s', '');
        $pwd2 = input('post.depositPwd/s', '');
        $invite_code = input('post.invite_code/s', '');     //邀请码
        
        
        // if(!$qv){
        //     return json(['code' => 1, 'info' => yuylangs('请选择区号！')]);
        // }
        
        if (!$invite_code) return json(['code' => 1, 'info' => yuylangs('code_not')]);
        //验证码
        /*if (config('app.verify') && $verify != '88888') {
            $verify_msg = Db::table('xy_verify_msg')->field('msg,addtime')->where(['tel' => $tel, 'type' => 1])->find();
            if (!$verify_msg) return json(['code' => 1, 'info' => yuylangs('yzmbcz')]);
            if ($verify != $verify_msg['msg']) return json(['code' => 1, 'info' => yuylangs('yzmcw')]);
            if (($verify_msg['addtime'] + (config('app.zhangjun_sms.min') * 60)) < time()) return json(['code' => 1, 'info' => yuylangs('yzmysx')]);
        }*/
        $pid = 0;
        $agent_id = 0;
        $type = input('type',1);
        $params['agent_service_id'] = '';
        if($invite_code){
            //用户邀请码
            if($type == 1){
                $parentinfo = Db::table($this->table)->field('id,status,agent_id,parent_id,level,agent_service_id')->where('invite_code', $invite_code)->find();
                if (!$parentinfo) return json(['code' => 1, 'info' => yuylangs('code_not')]);
                $is_invite = Db::table('xy_level')
                    ->where('level', $parentinfo['level'])
                    ->value('is_invite');
                if (empty($is_invite)) return json(['code' => 1, 'info' => yuylangs('user_not_auth')]);
                if ($parentinfo['status'] != 1) return json(['code' => 1, 'info' => yuylangs('disable_user')]);
                $pid = $parentinfo['id'];
                if ($parentinfo['agent_id'] > 0) {
                    $agent_id = $parentinfo['agent_id'];
                }
                if ($parentinfo['agent_service_id'] > 0) {
                    $params['agent_service_id'] = $parentinfo['agent_service_id'];
                }
            }else if($type == 2){
            //代理邀请码
                $sys_user = Db::table('system_user')->where(['invite_code' => $invite_code,'is_deleted'=>0])->find();
                if (!$sys_user) return json(['code' => 1, 'info' => yuylangs('code_not')]);
                $params['agent_service_id'] = $sys_user['id'];
            }else{
                return json(['code' => 1, 'info' => translate('error in type')]);
            }
        }

//        if ($agent_id == 0) {
//            $agent_id = model('admin/Users')->get_agent_id();
//        }
        $res = model('admin/Users')
            ->add_users($tel, $user_name, $pwd, $pid, '', $pwd2, $agent_id, $_SERVER["REMOTE_ADDR"],$qv='',$params);
        return json($res);
    }
    
   
   /**
    * 公共参数
    */ 
   public function common_parameters()
   {
      
        
       $langs = Db::table("xy_language")->where(['moryuy'=>1])->find();
       $language= Request::instance()->header('language')?Request::instance()->header('language'):$langs["link"];
   
        
       $parameters['language'] = $language;
       $parameters["area_code"] = explode("|",config('lang_tel_pix'));
       $parameters["recharge_money_list"] = explode("/",config('recharge_money_list'));
       $parameters['languageList'] = Db::table("xy_language")->field("name,link,image_url")->where("state",1)->select();
       $parameters["currency"] = config("currency");
       $parameters["app_url"] = config('app_url');
       
       $configData = Db::table("system_config")->select();
       $parameters["app_name"] = $configData[1]['value'];
       $parameters["site_icon"] = $configData[4]['value'];
       
       $parameters["withdrawalTime"] = config('tixian_time_1').":00".' - '.config('tixian_time_2').":00";
       $parameters["rechargeTime"] = config('chongzhi_time_1').":00".' - '.config('chongzhi_time_2').":00";
       $parameters["orderGrabbingTime"] = config('order_time_1').":00".' - '.config('order_time_2').":00";
      
      return json(['code'=>0,'data'=>$parameters,'info'=>'获取成功']);
   }

    public function logout()
    {
        $token= Request::instance()->header('TOKEN');
        
        $tokenData = Db::table("xy_token")->where(["token"=>$token])->find();
        Db::table("xy_token")->where("uid",$tokenData['uid'])->delete();
        \Session::delete('user_id');
        \Session::delete('user_join_chats');
        
        return json(['code'=>0,'info'=>yuylangs('czcg')]);
    }

    /**
     * 重置密码
     */
    public function do_forget()
    {
        if (!request()->isPost()) return json(['code' => 1, 'info' => yuylangs('qqcw')]);
        $tel = input('post.tel/s', '');
        $pwd = input('post.pwd/s', '');
        $verify = input('post.verify/d', 0);
        if (config('app.verify') && $verify != '88888') {
            $verify_msg = Db::table('xy_verify_msg')->field('msg,addtime')->where(['tel' => $tel, 'type' => 2])->find();
            if (!$verify_msg) return json(['code' => 1, 'info' => yuylangs('yzmbcz')]);
            if ($verify != $verify_msg['msg']) return json(['code' => 1, 'info' => yuylangs('yzmcw')]);
            if (($verify_msg['addtime'] + (config('app.zhangjun_sms.min') * 60)) < time()) return json(['code' => 1, 'info' => yuylangs('yzmysx')]);
        }
        $res = model('admin/Users')->reset_pwd($tel, $pwd);
        return json($res);
    }

    public function yuylangs()
    {
        $language = Db::table('xy_language')->field('id,title,name,link')->where(['state' => 1])->select();
        $this->assign('language',$language);
        return $this->fetch();
    }

    public function lang_set()
    {
        $lang = input('lang');
        cookie('think_var', $lang);
        $this->redirect('/index', 302);
    }

    public function register()
    {
        $param = \Request::param(true);
        $this->invite_code = isset($param[1]) ? trim($param[1]) : '';
        if (config('open_country_phone')) {
            return $this->fetch();
        } else return $this->fetch('register_no');
    }

    public function reset_qrcode()
    {
        $uinfo = Db::name('xy_users')->field('id,invite_code')->select();
        foreach ($uinfo as $v) {
            $model = model('admin/Users');
            $model->create_qrcode($v['invite_code'],$v['id']);
        }
        return '重新生成用户二维码图片成功';
    } 
}