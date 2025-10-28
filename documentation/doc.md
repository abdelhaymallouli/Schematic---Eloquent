# 🧠 **Eloquent Mastery: 2.1.1 → 2.1.3**


---

## **CHAPTER 1: 2.1.1 — Migrations & Eloquent Models**
### 🎯 Goal: Create the database structure and PHP models for `User`, `Article`, `Tag`, and the pivot table.

### 📦 What You Get
- 4 database tables  
- 3 Eloquent models  
- Foreign keys + constraints  
- Ready for relationships  

---

### **Step-by-Step Code (with comments)**

#### 1. Create Migration Files
```bash
# Create migrations
php artisan make:migration create_articles_table
php artisan make:migration create_tags_table
php artisan make:migration create_article_tag_table

# Create models
php artisan make:model Article
php artisan make:model Tag
````

---

#### 2. Migration: `create_articles_table.php`

```php
Schema::create('articles', function (Blueprint $table) {
    $table->id(); // Primary key

    // Foreign key to users table
    $table->foreignId('user_id')
          ->constrained()        // Links to users.id
          ->cascadeOnDelete();   // Delete articles if user deleted

    $table->string('title', 180);
    $table->string('slug', 200)->unique();  // Unique URL slug
    $table->text('excerpt')->nullable();
    $table->longText('content')->nullable();
    $table->timestamps();
});
```

---

#### 3. Migration: `create_tags_table.php`

```php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->timestamps();
});
```

---

#### 4. Migration: `create_article_tag_table.php` (Pivot)

```php
Schema::create('article_tag', function (Blueprint $table) {
    $table->foreignId('article_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

    // Prevent duplicate combinations
    $table->primary(['article_id', 'tag_id']);
});
```

---

#### 5. Model: `app/Models/Article.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'content'
    ];
}
```

---

#### 6. Model: `app/Models/Tag.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];
}
```

---

#### 7. Run Migrations

```bash
php artisan migrate
```

---

#### ✅ Verification in Tinker

```bash
php artisan tinker
>>> App\Models\Article::count(); // 0
>>> App\Models\Tag::create(['name' => 'Test', 'slug' => 'test']);
```

✅ **Database structure ready!**

---

## **CHAPTER 2: 2.1.2 — Eloquent Relationships**

### 🎯 Goal: Connect models so you can navigate data easily.

### 📦 What You Get

* `User → Articles` (1-n)
* `Article ↔ Tag` (n-n)
* Eager loading
* Relationship counts

---

### **Code with Comments**

#### 1. 1-n: User has many Articles

**`app/Models/User.php`**

```php
public function articles()
{
    return $this->hasMany(Article::class);
}
```

**`app/Models/Article.php`**

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

---

#### 2. n-n: Article ↔ Tag

**`app/Models/Article.php`**

```php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

**`app/Models/Tag.php`**

```php
public function articles()
{
    return $this->belongsToMany(Article::class);
}
```

---

#### 3. Eager Loading (Avoid N+1)

```php
// BAD
$articles = App\Models\Article::all();
foreach ($articles as $a) { $a->user; }

// GOOD
$articles = App\Models\Article::with(['user', 'tags'])->get();
```

---

#### 4. Count Related Records

```php
$articles = App\Models\Article::withCount('tags')->get();

foreach ($articles as $a) {
    echo $a->title . " ({$a->tags_count} tags)";
}
```

---

#### 5. Test in Tinker

```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->articles;

>>> $article = App\Models\Article::first();
>>> $article->user->name;
>>> $article->tags->pluck('name');

>>> App\Models\Tag::first()->articles;
```

✅ **Relationships fully functional!**

---

## **CHAPTER 3: 2.1.3 — Seeders & Factories**

### 🎯 Goal: Auto-generate 5 users, 20 articles, 10 tags with working links.

### 📦 What You Get

* Realistic fake data
* Relationships populated
* Single command setup

---

### **Step-by-Step Code**

#### 1. Create Factories

```bash
php artisan make:factory UserFactory --model=User
php artisan make:factory TagFactory --model=Tag
php artisan make:factory ArticleFactory --model=Article
```

---

#### 2. Factory: `UserFactory.php`

```php
public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'remember_token' => Str::random(10),
    ];
}
```

---

#### 3. Factory: `TagFactory.php`

```php
$name = fake()->unique()->word();

return [
    'name' => ucfirst($name),
    'slug' => Str::slug($name),
];
```

---

#### 4. Factory: `ArticleFactory.php`

```php
$title = fake()->unique()->sentence(4);

return [
    'user_id' => User::inRandomOrder()->value('id') ?? 1,
    'title' => $title,
    'slug' => Str::slug($title),
    'excerpt' => fake()->sentence(12),
    'content' => fake()->paragraphs(3, true),
];
```

---

#### 5. Create Seeders

```bash
php artisan make:seeder UserSeeder
php artisan make:seeder TagSeeder
php artisan make:seeder ArticleSeeder
php artisan make:seeder PivotArticleTagSeeder
```

---

#### 6. Seeder: `UserSeeder.php`

```php
public function run(): void
{
    User::factory()->count(5)->create();
}
```

---

#### 7. Seeder: `TagSeeder.php`

```php
Tag::factory()->count(10)->create();
```

---

#### 8. Seeder: `ArticleSeeder.php`

```php
Article::factory()->count(20)->create();
```

---

#### 9. Seeder: `PivotArticleTagSeeder.php`

```php
public function run(): void
{
    $tagIds = Tag::pluck('id');

    Article::all()->each(function ($article) use ($tagIds) {
        $article->tags()->sync(
            $tagIds->random(rand(1, 4))->all()
        );
    });
}
```

---

#### 10. Final `DatabaseSeeder.php`

```php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        TagSeeder::class,
        ArticleSeeder::class,
        PivotArticleTagSeeder::class,
    ]);
}
```

---

#### 11. Run Everything

```bash
php artisan migrate:fresh --seed --verbose
```

**Output:**

```
Seeding: UserSeeder
Seeding: TagSeeder
Seeding: ArticleSeeder
Seeding: PivotArticleTagSeeder
```

---

#### 12. Final Test

```bash
php artisan tinker
>>> App\Models\User::count();     // 5
>>> App\Models\Article::count();  // 20
>>> App\Models\Tag::count();      // 10

>>> App\Models\User::first()->articles->count();  // 3–6
>>> App\Models\Article::first()->tags->pluck('name');
```

---

## ✅ **SUMMARY: What You Now Have**

| Feature          | Status        | Command                |
| ---------------- | ------------- | ---------------------- |
| Tables           | ✅ Created     | `migrate`              |
| Models           | ✅ Ready       | `make:model`           |
| 1-n Relationship | ✅ Working     | `$user->articles`      |
| n-n Relationship | ✅ Working     | `$article->tags`       |
| Fake Data        | ✅ 5 + 20 + 10 | `migrate:fresh --seed` |

---

### 🚀 **Your Final Workflow**

```bash
# 1. Reset + Fill DB
php artisan migrate:fresh --seed

# 2. Test
php artisan tinker
>>> App\Models\User::first()->articles
>>> App\Models\Article::first()->tags->pluck('name')
```


# Chapter 4: 2.1.4 — CRUD Queries with Eloquent

**Goal:** Create, Read, Update, Delete data using Eloquent in Tinker  
**Prerequisites:** Database filled via `migrate:fresh --seed` (from 2.1.3)

---

## What You Get

- Full CRUD control
- Relationship manipulation
- Query chaining
- Custom scopes
- Real-world examples

---

## Step-by-Step Code (with comments)

### 1. Launch Tinker
```bash
php artisan tinker
````

Interactive Laravel console – run Eloquent like magic.

---

### 2. CREATE (C in CRUD)

#### Method 1: `create()` – One-liner

```php
use App\Models\Article;

$article = Article::create([
    'user_id' => 1,                     // Must exist in users table
    'title'   => 'Premier article manuel',
    'slug'    => 'premier-article',     // Must be unique
    'excerpt' => 'Introduction à Eloquent CRUD',
    'content' => 'Ceci est un test d’ajout via Tinker.'
]);
```

⚠️ Fields must be in `$fillable` in `Article.php`.

#### Method 2: `new` + `save()` – Step-by-step

```php
$a = new Article;
$a->user_id = 1;
$a->title   = 'Deuxième article';
$a->slug    = 'deuxieme-article';
$a->save(); // Saves to DB
```

**Verify**

```php
Article::count(); // → e.g. 21 (20 from seeder + 1 new)
```

---

### 3. READ (R in CRUD)

**All articles**

```php
Article::all(); // → Collection of all articles
```

**Find by ID**

```php
Article::find(1); // → Article with ID 1 or null
```

**Filter with where**

```php
Article::where('title', 'like', '%article%')->get();
```

**With relationships (eager load)**

```php
Article::with(['user', 'tags'])->first();
```

**Count articles per user**

```php
\App\Models\User::withCount('articles')->get(); 
// → Each user has `articles_count` field
```

---

### 4. UPDATE (U in CRUD)

#### Method 1: `update()`

```php
$article = Article::find(1);
$article->update(['title' => 'Titre modifié']);
```

#### Method 2: `save()`

```php
$article->title = 'Nouveau titre modifié';
$article->save();
```

**Verify**

```php
Article::find(1)->title; // → "Nouveau titre modifié"
```

---

### 5. DELETE (D in CRUD)

```php
$article = Article::find(1);
$article->delete();
```

**Verify**

```php
Article::find(1); // → null
```

---

### 6. MANAGE RELATIONSHIPS (n-n)

**Add tag to article**

```php
$a = Article::first();
$a->tags()->attach(1); // Attach tag ID 1
```

**Remove tag**

```php
$a->tags()->detach(1); // Remove tag ID 1
```

**Replace all tags**

```php
$a->tags()->sync([2, 3, 4]); 
// → Only tags 2,3,4 remain (others removed)
```

---

### 7. QUERY CHAINING (Filter + Sort + Limit)

**Latest 5 articles**

```php
Article::orderBy('created_at', 'desc')->take(5)->get();
```

**Select specific columns**

```php
Article::select('id', 'title', 'slug')->get();
```

**Combine conditions**

```php
Article::where('user_id', 1)
       ->orderBy('title')
       ->limit(3)
       ->get();
```

---

### 8. CUSTOM SCOPE (Reusable Query)

In `app/Models/Article.php`:

```php
public function scopeRecent($query)
{
    return $query->orderBy('created_at', 'desc')->take(5);
}
```

**Use in Tinker**

```php
Article::recent()->get(); // → Last 5 articles
```

---

### 9. BONUS: Advanced Query (Raw + Group By)

```php
Article::where('title', 'like', '%laravel%')
       ->selectRaw('user_id, count(*) as total')
       ->groupBy('user_id')
       ->get();
// → How many Laravel articles per user
```

---

## SUMMARY: CRUD Methods

| Action        | Eloquent Example             |
| ------------- | ---------------------------- |
| Create        | `create()` or `save()`       |
| Read          | `all()`, `find()`, `where()` |
| Update        | `update()` or `save()`       |
| Delete        | `delete()`                   |
| Relationships | `attach()`, `sync()`         |
| Scopes        | `scopeName()`                |

---

## Your Final Tinker Workflow

```bash
php artisan tinker

// CREATE
Article::create(['user_id'=>1, 'title'=>'Test', 'slug'=>'test']);

// READ
Article::with(['user','tags'])->first();

// UPDATE
$a = Article::find(1); 
$a->title = 'Updated'; 
$a->save();

// DELETE
$a->delete();

// RELATIONSHIPS
$a->tags()->attach(1);
$a->tags()->sync([2,3]);

// SCOPE
Article::recent()->get();
```

---

## Your Final Workflow (Copy-Paste)

```bash
# 1. Reset + Fill DB
php artisan migrate:fresh --seed

# 2. Test CRUD
php artisan tinker
>>> Article::create(['user_id'=>1, 'title'=>'New', 'slug'=>'new'])
>>> Article::find(1)->update(['title'=>'Changed'])
>>> Article::find(1)->delete()
>>> Article::recent()->get()
```

You are **100% ready** for real-world Laravel apps.

```

---

If you want, I can also make a **super condensed "Tinker cheat sheet"** in Markdown that fits **all CRUD + relationships + scopes in one small page**, perfect for quick reference.  

Do you want me to do that?
```
