<?php

date_default_timezone_set('Asia/Gaza'); 


$products = [
  ["id"=>1,"name"=>"USB","description"=>"USB 2.0/3 64GB","price"=>25.00,"category"=>"اكسسوارات","image"=>"https://shop.sandisk.com/content/dam/store/en-us/assets/products/usb-flash-drives/cruzer-blade-usb-2-0/gallery/cruzer-blade-usb-2-0-angle.png.wdthumb.1280.1280.webp"],
  ["id"=>2,"name"=>"فأرة","description"=>"لاسلكية","price"=>10.00,"category"=>"طرفيات","image"=>"https://estore.jawwal.ps/storage/product/18845/rIt98Zf2Jfak4JyGqIAjMZmd7AHpuOrALqgwyoD2.png"],
];

$categories = ["اكسسوارات","طرفيات","تخزين","صوتيات","أخرى"];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function nextId(array $list){
  $m=0; foreach($list as $p){ if($p['id']>$m) $m=$p['id']; }
  return $m+1;
}


$form = [
  "name" => "",
  "description" => "",
  "price" => "",
  "category" => "",
  "image" => "", 

$errors = [];
$success = "";


$isEdit = false;
$editId = null;



if ($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST["delete_id"])) {
  $del = (int)($_POST["delete_id"] ?? 0);
  $products = array_values(array_filter($products, fn($p)=>$p['id'] !== $del));
  $success = "تم حذف المنتج (ID: {$del}) بنجاح.";
}



if ($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST["save_action"])) {

  $form["name"] = trim($_POST["name"] ?? "");
  $form["description"] = trim($_POST["description"] ?? "");
  $form["price"] = trim($_POST["price"] ?? "");
  $form["category"] = trim($_POST["category"] ?? "");
  $form["image"] = trim($_POST["image"] ?? "");
  $currentId = isset($_POST["current_id"]) && $_POST["current_id"]!=="" ? (int)$_POST["current_id"] : null;
  $isEdit = $currentId !== null;


  if ($form["name"]==="")        $errors["name"] = "الاسم مطلوب";
  if ($form["description"]==="") $errors["description"] = "الوصف مطلوب";
  if ($form["price"]==="")       $errors["price"] = "السعر مطلوب";
  elseif(!is_numeric($form["price"]) || (float)$form["price"]<=0) $errors["price"]="السعر يجب أن يكون رقمًا أكبر من صفر";
  if ($form["category"]==="")    $errors["category"] = "يجب اختيار فئة";
  elseif(!in_array($form["category"], $categories, true)) $errors["category"]="فئة غير صالحة";


  if ($form["image"]!=="") {

    if (!preg_match('~^https?://~i', $form["image"])) {
      $errors["image"] = "رابط الصورة يجب أن يبدأ بـ http:// أو https://";
    }
  }

  if (!$errors) {
    if ($isEdit) {

      foreach ($products as &$p){
        if ($p['id']===$currentId){
          $p['name'] = $form['name'];
          $p['description'] = $form['description'];
          $p['price'] = (float)$form['price'];
          $p['category'] = $form['category'];
          $p['image'] = $form['image'];
          break;
        }
      }
      unset($p);
      $success = "تم تحديث المنتج (ID: {$currentId}) بنجاح.";


      $form = ["name"=>"","description"=>"","price"=>"","category"=>"","image"=>""];
      $isEdit = false;
      $editId = null;

    } else {
  
      $newId = nextId($products);
      $products[] = [
        "id"=>$newId,
        "name"=>$form["name"],
        "description"=>$form["description"],
        "price"=>(float)$form["price"],
        "category"=>$form["category"],
        "image"=>$form["image"],
      ];
      $success = "تمت إضافة المنتج بنجاح (ID: {$newId}).";

  
      $form = ["name"=>"","description"=>"","price"=>"","category"=>"","image"=>""];
    }
  }
}



if ($_SERVER["REQUEST_METHOD"]==="GET" && isset($_GET["edit_id"])) {
  $editId = (int)$_GET["edit_id"];
  foreach ($products as $p){
    if ($p['id']===$editId){
      $isEdit = true;
      $form["name"] = $p["name"];
      $form["description"] = $p["description"];
      $form["price"] = (string)$p["price"];
      $form["category"] = $p["category"];
      $form["image"] = $p["image"];
      break;
    }
  }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة المنتجات (أسلوب طالب)</title>

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">
</head>
<body class="container py-4">

  <h2 class="mb-3">قائمة المنتجات</h2>


  <?php if($success): ?>
    <div class="alert alert-success"><?=h($success)?></div>
  <?php endif; ?>
  <?php if($errors): ?>
    <div class="alert alert-danger">يرجى تصحيح الأخطاء أدناه</div>
  <?php endif; ?>

  <div class="row g-4">

    <div class="col-12 col-lg-7">
      <div class="card">
        <div class="card-header fw-bold">المنتجات</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead class="table-light">
                <tr>
                  <th>المعرف</th>
                  <th>الصورة</th>
                  <th>الاسم</th>
                  <th>الوصف</th>
                  <th class="text-end">السعر ($)</th>
                  <th>الفئة</th>
                  <th class="text-center">إجراءات</th>
                </tr>
              </thead>
              <tbody>
                <?php if(!$products): ?>
                  <tr><td colspan="7" class="text-center text-muted">لا توجد منتجات</td></tr>
                <?php else: foreach($products as $p): ?>
                  <tr>
                    <td><?=h($p['id'])?></td>
                    <td>
                      <?php if(!empty($p['image'])): ?>
                    <img src="<?=h($p['image'])?>" alt="img"
     style="width:100px;height:100px;object-fit:contain;background:#fff;border:1px solid #ddd;border-radius:6px">

                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td><?=h($p['name'])?></td>
                    <td><?=h($p['description'])?></td>
                    <td class="text-end"><?=number_format((float)$p['price'],2)?></td>
                    <td><?=h($p['category'])?></td>
                    <td class="text-center">
                      <a class="btn btn-sm btn-outline-primary me-1" href="?edit_id=<?=h($p['id'])?>">تعديل</a>

                 
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModal"
                        data-id="<?=h($p['id'])?>"
                        data-name="<?=h($p['name'])?>"
                      >حذف</button>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

 
    <div class="col-12 col-lg-5">
      <div class="card">
        <div class="card-header fw-bold"><?= $isEdit ? "تعديل المنتج" : "إضافة منتج جديد" ?></div>
        <div class="card-body">
          <form method="post" novalidate>
    
            <div class="mb-3">
              <label class="form-label">الاسم*</label>
              <input
                class="form-control <?=isset($errors['name'])?'is-invalid':''?>"
                name="name"
                value="<?=h($form['name'])?>"
                placeholder="اكتب اسم المنتج">
              <?php if(isset($errors['name'])): ?>
                <div class="invalid-feedback"><?=h($errors['name'])?></div>
              <?php endif; ?>
            </div>

      
            <div class="mb-3">
              <label class="form-label">الوصف*</label>
              <textarea
                class="form-control <?=isset($errors['description'])?'is-invalid':''?>"
                name="description"
                rows="3"
                placeholder="اكتب وصفًا مختصرًا"><?=h($form['description'])?></textarea>
              <?php if(isset($errors['description'])): ?>
                <div class="invalid-feedback"><?=h($errors['description'])?></div>
              <?php endif; ?>
            </div>

         
            <div class="mb-3">
              <label class="form-label">السعر ($)*</label>
              <input
                class="form-control <?=isset($errors['price'])?'is-invalid':''?>"
                name="price"
                value="<?=h($form['price'])?>"
                placeholder="مثال: 9.99">
              <?php if(isset($errors['price'])): ?>
                <div class="invalid-feedback"><?=h($errors['price'])?></div>
              <?php endif; ?>
            </div>

         
            <div class="mb-3">
              <label class="form-label">الفئة*</label>
              <select
                class="form-select <?=isset($errors['category'])?'is-invalid':''?>"
                name="category">
                <option value="">-- اختر الفئة --</option>
                <?php foreach($categories as $c): ?>
                  <option value="<?=h($c)?>" <?=($form['category']===$c?'selected':'')?>><?=h($c)?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($errors['category'])): ?>
                <div class="invalid-feedback"><?=h($errors['category'])?></div>
              <?php endif; ?>
            </div>


            <div class="mb-3">
              <label class="form-label">رابط الصورة (اختياري)</label>
              <input
                class="form-control <?=isset($errors['image'])?'is-invalid':''?>"
                name="image"
                value="<?=h($form['image'])?>"
                placeholder="مثال: https://example.com/p.png">
              <?php if(isset($errors['image'])): ?>
                <div class="invalid-feedback"><?=h($errors['image'])?></div>
              <?php endif; ?>
            </div>

            <?php if($isEdit): ?>
              <input type="hidden" name="current_id" value="<?=h($editId)?>">
            <?php endif; ?>

            <button class="btn btn-primary w-100" name="save_action" value="1">
              <?= $isEdit ? "تحديث" : "إضافة" ?>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">تأكيد الحذف</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">هل أنت متأكد أنك تريد حذف: <strong id="delName">—</strong> ؟</p>
          <input type="hidden" name="delete_id" id="delId" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button class="btn btn-danger">حذف</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>

  const deleteModal = document.getElementById('deleteModal');
  deleteModal.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');
    document.getElementById('delId').value = id;
    document.getElementById('delName').textContent = name;
  });
  </script>
</body>
</html>
