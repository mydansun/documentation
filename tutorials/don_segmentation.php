<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Difference of Normals Based Segmentation &#8212; PCL 0.0 documentation</title>
    <link rel="stylesheet" href="_static/sphinxdoc.css" type="text/css" />
    <link rel="stylesheet" href="_static/pygments.css" type="text/css" />
    <script id="documentation_options" data-url_root="./" src="_static/documentation_options.js"></script>
    <script src="_static/jquery.js"></script>
    <script src="_static/underscore.js"></script>
    <script src="_static/doctools.js"></script>
    <script src="_static/language_data.js"></script>
    <link rel="search" title="Search" href="search.php" />
<?php
define('MODX_CORE_PATH', '/var/www/pointclouds.org/core/');
define('MODX_CONFIG_KEY', 'config');

require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');

$snip = $modx->runSnippet("getSiteNavigation", array('id'=>5, 'phLevels'=>'sitenav.level0,sitenav.level1', 'showPageNav'=>'n'));
$chunkOutput = $modx->getChunk("site-header", array('sitenav'=>$snip));
$bodytag = str_replace("[[+showSubmenus:notempty=`", "", $chunkOutput);
$bodytag = str_replace("`]]", "", $bodytag);
echo $bodytag;
echo "\n";
?>
<div id="pagetitle">
<h1>Documentation</h1>
<a id="donate" href="http://www.openperception.org/support/"><img src="/assets/images/donate-button.png" alt="Donate to the Open Perception foundation"/></a>
</div>
<div id="page-content">

  </head><body>

    <div class="document">
      <div class="documentwrapper">
          <div class="body" role="main">
            
  <div class="section" id="difference-of-normals-based-segmentation">
<span id="don-segmentation"></span><h1><a class="toc-backref" href="#id7">Difference of Normals Based Segmentation</a></h1>
<p>In this tutorial we will learn how to use Difference of Normals features, implemented in the <code class="docutils literal notranslate"><span class="pre">pcl::DifferenceOfNormalsEstimation</span></code> class, for scale-based segmentation of unorganized point clouds.</p>
<p>This algorithm performs a scale based segmentation of the given input point cloud, finding points that belong within the scale parameters given.</p>
<div class="figure align-center" id="id4">
<img alt="_images/donpipelinesmall.jpg" src="_images/donpipelinesmall.jpg" />
<p class="caption"><span class="caption-text">Overview of the pipeline in DoN segmentation.</span></p>
</div>
<div class="contents topic" id="contents">
<p class="topic-title">Contents</p>
<ul class="simple">
<li><p><a class="reference internal" href="#difference-of-normals-based-segmentation" id="id7">Difference of Normals Based Segmentation</a></p>
<ul>
<li><p><a class="reference internal" href="#theoretical-primer" id="id8">Theoretical Primer</a></p></li>
<li><p><a class="reference internal" href="#using-difference-of-normals-for-segmentation" id="id9">Using Difference of Normals for Segmentation</a></p></li>
<li><p><a class="reference internal" href="#the-data-set" id="id10">The Data Set</a></p></li>
<li><p><a class="reference internal" href="#the-code" id="id11">The Code</a></p>
<ul>
<li><p><a class="reference internal" href="#compiling-and-running-the-program" id="id12">Compiling and running the program</a></p></li>
</ul>
</li>
<li><p><a class="reference internal" href="#the-explanation" id="id13">The Explanation</a></p>
<ul>
<li><p><a class="reference internal" href="#large-small-radius-normal-estimation" id="id14">Large/Small Radius Normal Estimation</a></p></li>
<li><p><a class="reference internal" href="#difference-of-normals-feature-calculation" id="id15">Difference of Normals Feature Calculation</a></p></li>
<li><p><a class="reference internal" href="#difference-of-normals-based-filtering" id="id16">Difference of Normals Based Filtering</a></p></li>
<li><p><a class="reference internal" href="#clustering-the-results" id="id17">Clustering the Results</a></p></li>
<li><p><a class="reference internal" href="#references-further-information" id="id18">References/Further Information</a></p></li>
</ul>
</li>
</ul>
</li>
</ul>
</div>
<div class="section" id="theoretical-primer">
<h2><a class="toc-backref" href="#id8">Theoretical Primer</a></h2>
<p>The Difference of Normals (DoN) provides a computationally efficient, multi-scale approach to processing large unorganized 3D point clouds. The idea is very simple in concept, and yet surprisingly effective in the segmentation of scenes with a wide variation of scale. For each point <img class="math" src="_images/math/ea886a48be60dbeaaac677a61588152d4b4dbcdc.png" alt="$p$"/> in a pointcloud <img class="math" src="_images/math/4b716a1a763aa895c855bfd2a986615197198821.png" alt="$P$"/>, two unit point normals <img class="math" src="_images/math/9dc3fddec428e377dd7ac1a5432537dfa125fe10.png" alt="$\hat{\mathbf{n}}(\mathbf{p}, r_l), \hat{\mathbf{n}}(\mathbf{p}, r_s)$"/> are estimated with different radii, <img class="math" src="_images/math/ed9f04f6ab14d15893f1bbb5804680503618d276.png" alt="$r_l &gt; r_s$"/> . The normalized (vector) difference of these point normals defines the operator.</p>
<p>Formally the Difference of Normals operator is defined,</p>
<blockquote>
<div><p class="centered">
<strong><img class="math" src="_images/math/e1f1559d153a0263ffe8ccdf3fd76212c2786388.png" alt="\mathbf{\Delta}\mathbf{\hat{n}}(p, r_s, r_l) = \frac{\mathbf{\hat{n}}(p, r_s) - \mathbf{\hat{n}}(p, r_l)}{2}"/></strong></p></div></blockquote>
<p>where <img class="math" src="_images/math/07b184ef455cc6f0d25af1374ce2f8fd8030e619.png" alt="$r_s, r_l \in \mathbb{R}$"/>, <img class="math" src="_images/math/6b3ac6d2879e25dce84457a9b72698e6e86fe852.png" alt="$r_s&lt;r_l$"/>, and <img class="math" src="_images/math/00e2a3ae22ff4d3ab85084fe2fc348ce2107ea0d.png" alt="$\mathbf{\hat{n}}(p, r)$"/> is the surface normal estimate at point <img class="math" src="_images/math/ea886a48be60dbeaaac677a61588152d4b4dbcdc.png" alt="$p$"/>, given the support radius <img class="math" src="_images/math/bf710c11d7f48da6fc4aeadb36129ef00af65be9.png" alt="$r$"/>. Notice, the response of the operator is a normalized vector field, and is thus orientable (the resulting direction is a key feature), however the operator’s norm often provides an easier quantity to work with, and is always in the range <img class="math" src="_images/math/a54516be65122ced1659d08df2f90b69a981136f.png" alt="(0,1)"/>.</p>
<div class="figure align-center" id="id5">
<a class="reference internal image-reference" href="_images/don_scalenormals.svg"><img alt="Illustration of the effect of support radius on estimated surface normals for a point cloud." src="_images/don_scalenormals.svg" width="60%" /></a>
<p class="caption"><span class="caption-text">Illustration of the effect of support radius on estimated surface normals for a point cloud.</span></p>
</div>
<p>The primary motivation behind DoN is the observation that surface normals estimated at any given radius reflect the underlying geometry of the surface at the scale of the support radius. Although there are many different methods of estimating the surface normals, normals are always estimated with a support radius (or via a fixed number of neighbours). This support radius determines the scale in the surface structure which the normal represents.</p>
<p>The above diagram illustrates this effect in 1D. Normals, <img class="math" src="_images/math/c4f37ba470aeac1b5c656b37c91ccc122701db92.png" alt="$\mathbf{\hat{n}}$"/>, and tangents, <img class="math" src="_images/math/891383f055a4cbd032f88c723a668ac3fea6b011.png" alt="$T$"/>, estimated with a small support radius <img class="math" src="_images/math/02ea62bb171aebf91db244059909f312f37fe640.png" alt="$r_s$"/> are affected by small-scale surface structure (and similarly by noise). On the other hand, normals and tangent planes estimated with a large support radius $r_l$ are less affected by small-scale structure, and represent the geometry of larger scale surface structures. In fact a similar set of features is seen in the DoN feature vectors for real-world street curbs in a LiDAR image shown below.</p>
<div class="figure align-center" id="id6">
<img alt="_images/don_curb_closeup_small.jpg" src="_images/don_curb_closeup_small.jpg" />
<p class="caption"><span class="caption-text">Closeup of the DoN feature vectors calculated for a LiDAR pointcloud of a street curb.</span></p>
</div>
<p>For more comprehensive information, please refer to the article <a class="reference internal" href="#don2012" id="id1"><span>[DON2012]</span></a>.</p>
</div>
<div class="section" id="using-difference-of-normals-for-segmentation">
<h2><a class="toc-backref" href="#id9">Using Difference of Normals for Segmentation</a></h2>
<p>For segmentation we simply perform the following:</p>
<blockquote>
<div><ol class="arabic simple">
<li><p>Estimate the normals for every point using a large support radius of <img class="math" src="_images/math/040ad0ff5edc211d0c77249a3fd1effbffe597f3.png" alt="r_l"/></p></li>
<li><p>Estimate the normals for every point using the small support radius of <img class="math" src="_images/math/8c841a4fa812c0e6775d8dc9252d7c8021b599b1.png" alt="r_s"/></p></li>
<li><p>For every point the normalized difference of normals for every point, as defined above.</p></li>
<li><p>Filter the resulting vector field to isolate points belonging to the scale/region of interest.</p></li>
</ol>
</div></blockquote>
</div>
<div class="section" id="the-data-set">
<h2><a class="toc-backref" href="#id10">The Data Set</a></h2>
<p>For this tutorial we suggest the use of publicly available (creative commons licensed) urban LiDAR data from the <a class="reference internal" href="#kitti" id="id2"><span>[KITTI]</span></a> project. This data is collected from a Velodyne LiDAR scanner mounted on a car, for the purpose of evaluating self-driving cars. To convert the data set to PCL compatible point clouds please see <a class="reference internal" href="#kittipcl" id="id3"><span>[KITTIPCL]</span></a>. Examples and an example data set will be posted here in future as part of the tutorial.</p>
</div>
<div class="section" id="the-code">
<h2><a class="toc-backref" href="#id11">The Code</a></h2>
<p>Next what you need to do is to create a file <code class="docutils literal notranslate"><span class="pre">don_segmentation.cpp</span></code> in any editor you prefer and copy the following code inside of it:</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
  2
  3
  4
  5
  6
  7
  8
  9
 10
 11
 12
 13
 14
 15
 16
 17
 18
 19
 20
 21
 22
 23
 24
 25
 26
 27
 28
 29
 30
 31
 32
 33
 34
 35
 36
 37
 38
 39
 40
 41
 42
 43
 44
 45
 46
 47
 48
 49
 50
 51
 52
 53
 54
 55
 56
 57
 58
 59
 60
 61
 62
 63
 64
 65
 66
 67
 68
 69
 70
 71
 72
 73
 74
 75
 76
 77
 78
 79
 80
 81
 82
 83
 84
 85
 86
 87
 88
 89
 90
 91
 92
 93
 94
 95
 96
 97
 98
 99
100
101
102
103
104
105
106
107
108
109
110
111
112
113
114
115
116
117
118
119
120
121
122
123
124
125
126
127
128
129
130
131
132
133
134
135
136
137
138
139
140
141
142
143
144
145
146
147
148
149
150
151
152
153
154
155
156
157
158
159
160
161
162
163
164
165
166
167
168
169
170
171
172
173
174
175
176
177
178
179
180
181
182
183
184
185
186
187
188
189
190</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cm">/**</span>
<span class="cm"> * @file don_segmentation.cpp</span>
<span class="cm"> * Difference of Normals Example for PCL Segmentation Tutorials.</span>
<span class="cm"> *</span>
<span class="cm"> * @author Yani Ioannou</span>
<span class="cm"> * @date 2012-09-24</span>
<span class="cm"> */</span>
<span class="cp">#include</span> <span class="cpf">&lt;string&gt;</span><span class="cp"></span>

<span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/io/pcd_io.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/search/organized.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/search/kdtree.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/normal_3d_omp.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/filters/conditional_removal.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/segmentation/extract_clusters.h&gt;</span><span class="cp"></span>

<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/don.h&gt;</span><span class="cp"></span>

<span class="k">using</span> <span class="k">namespace</span> <span class="n">pcl</span><span class="p">;</span>
<span class="k">using</span> <span class="k">namespace</span> <span class="n">std</span><span class="p">;</span>

<span class="kt">int</span>
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span> <span class="o">*</span><span class="n">argv</span><span class="p">[])</span>
<span class="p">{</span>
  <span class="c1">///The smallest scale to use in the DoN filter.</span>
  <span class="kt">double</span> <span class="n">scale1</span><span class="p">;</span>

  <span class="c1">///The largest scale to use in the DoN filter.</span>
  <span class="kt">double</span> <span class="n">scale2</span><span class="p">;</span>

  <span class="c1">///The minimum DoN magnitude to threshold by</span>
  <span class="kt">double</span> <span class="n">threshold</span><span class="p">;</span>

  <span class="c1">///segment scene into clusters with given distance tolerance using euclidean clustering</span>
  <span class="kt">double</span> <span class="n">segradius</span><span class="p">;</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">argc</span> <span class="o">&lt;</span> <span class="mi">6</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;usage: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; inputfile smallscale largescale threshold segradius&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">exit</span> <span class="p">(</span><span class="n">EXIT_FAILURE</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">/// the file to read from.</span>
  <span class="n">string</span> <span class="n">infile</span> <span class="o">=</span> <span class="n">argv</span><span class="p">[</span><span class="mi">1</span><span class="p">];</span>
  <span class="c1">/// small scale</span>
  <span class="n">istringstream</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">2</span><span class="p">])</span> <span class="o">&gt;&gt;</span> <span class="n">scale1</span><span class="p">;</span>
  <span class="c1">/// large scale</span>
  <span class="n">istringstream</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">3</span><span class="p">])</span> <span class="o">&gt;&gt;</span> <span class="n">scale2</span><span class="p">;</span>
  <span class="n">istringstream</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">4</span><span class="p">])</span> <span class="o">&gt;&gt;</span> <span class="n">threshold</span><span class="p">;</span>   <span class="c1">// threshold for DoN magnitude</span>
  <span class="n">istringstream</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">5</span><span class="p">])</span> <span class="o">&gt;&gt;</span> <span class="n">segradius</span><span class="p">;</span>   <span class="c1">// threshold for radius segmentation</span>

  <span class="c1">// Load cloud in blob format</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCLPointCloud2</span> <span class="n">blob</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">io</span><span class="o">::</span><span class="n">loadPCDFile</span> <span class="p">(</span><span class="n">infile</span><span class="p">.</span><span class="n">c_str</span> <span class="p">(),</span> <span class="n">blob</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">fromPCLPointCloud2</span> <span class="p">(</span><span class="n">blob</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Create a search tree, use KDTreee for non-organized data.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">Search</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">isOrganized</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">tree</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">OrganizedNeighbor</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">tree</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="nb">false</span><span class="p">));</span>
  <span class="p">}</span>

  <span class="c1">// Set the input pointcloud for the search tree</span>
  <span class="n">tree</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="n">scale1</span> <span class="o">&gt;=</span> <span class="n">scale2</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error: Large scale must be &gt; small scale!&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">exit</span> <span class="p">(</span><span class="n">EXIT_FAILURE</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Compute normals using both small and large scales at each point</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimationOMP</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="cm">/**</span>
<span class="cm">   * NOTE: setting viewpoint is very important, so that we can ensure</span>
<span class="cm">   * normals are all pointed in the same direction!</span>
<span class="cm">   */</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setViewPoint</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">max</span> <span class="p">(),</span> <span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">max</span> <span class="p">(),</span> <span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">max</span> <span class="p">());</span>

  <span class="c1">// calculate normals with the small scale</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Calculating normals for scale...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scale1</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals_small_scale</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">scale1</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">normals_small_scale</span><span class="p">);</span>

  <span class="c1">// calculate normals with the large scale</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Calculating normals for scale...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scale2</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals_large_scale</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">scale2</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">normals_large_scale</span><span class="p">);</span>

  <span class="c1">// Create output cloud for DoN results</span>
  <span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">doncloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">doncloud</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Calculating DoN... &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="c1">// Create DoN operator</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">DifferenceOfNormalsEstimation</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">PointNormal</span><span class="p">,</span> <span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">don</span><span class="p">;</span>
  <span class="n">don</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">don</span><span class="p">.</span><span class="n">setNormalScaleLarge</span> <span class="p">(</span><span class="n">normals_large_scale</span><span class="p">);</span>
  <span class="n">don</span><span class="p">.</span><span class="n">setNormalScaleSmall</span> <span class="p">(</span><span class="n">normals_small_scale</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">don</span><span class="p">.</span><span class="n">initCompute</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error: Could not initialize DoN feature operator&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">exit</span> <span class="p">(</span><span class="n">EXIT_FAILURE</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Compute DoN</span>
  <span class="n">don</span><span class="p">.</span><span class="n">computeFeature</span> <span class="p">(</span><span class="o">*</span><span class="n">doncloud</span><span class="p">);</span>

  <span class="c1">// Save DoN features</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;don.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">doncloud</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span> 

  <span class="c1">// Filter by magnitude</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Filtering out DoN mag &lt;= &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">threshold</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="c1">// Build the condition for filtering</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionOr</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">range_cond</span> <span class="p">(</span>
    <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionOr</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="p">()</span>
    <span class="p">);</span>
  <span class="n">range_cond</span><span class="o">-&gt;</span><span class="n">addComparison</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="p">(</span>
                               <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;curvature&quot;</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ComparisonOps</span><span class="o">::</span><span class="n">GT</span><span class="p">,</span> <span class="n">threshold</span><span class="p">))</span>
                             <span class="p">);</span>
  <span class="c1">// Build the filter</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionalRemoval</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">condrem</span><span class="p">;</span>
  <span class="n">condrem</span><span class="p">.</span><span class="n">setCondition</span> <span class="p">(</span><span class="n">range_cond</span><span class="p">);</span>
  <span class="n">condrem</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">doncloud</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">doncloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Apply filter</span>
  <span class="n">condrem</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">doncloud_filtered</span><span class="p">);</span>

  <span class="n">doncloud</span> <span class="o">=</span> <span class="n">doncloud_filtered</span><span class="p">;</span>

  <span class="c1">// Save filtered output</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Filtered Pointcloud: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">doncloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;don_filtered.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">doncloud</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span> 

  <span class="c1">// Filter by magnitude</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Clustering using EuclideanClusterExtraction with tolerance &lt;= &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">segradius</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">segtree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">segtree</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">doncloud</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="n">cluster_indices</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">EuclideanClusterExtraction</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">ec</span><span class="p">;</span>

  <span class="n">ec</span><span class="p">.</span><span class="n">setClusterTolerance</span> <span class="p">(</span><span class="n">segradius</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setMinClusterSize</span> <span class="p">(</span><span class="mi">50</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setMaxClusterSize</span> <span class="p">(</span><span class="mi">100000</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">segtree</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">doncloud</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">cluster_indices</span><span class="p">);</span>

  <span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;::</span><span class="n">const_iterator</span> <span class="n">it</span> <span class="o">=</span> <span class="n">cluster_indices</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span> <span class="n">it</span> <span class="o">!=</span> <span class="n">cluster_indices</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">it</span><span class="p">,</span> <span class="n">j</span><span class="o">++</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_cluster_don</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
    <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;::</span><span class="n">const_iterator</span> <span class="n">pit</span> <span class="o">=</span> <span class="n">it</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span> <span class="n">pit</span> <span class="o">!=</span> <span class="n">it</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">pit</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">cloud_cluster_don</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">doncloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="o">*</span><span class="n">pit</span><span class="p">]);</span>
    <span class="p">}</span>

    <span class="n">cloud_cluster_don</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="kt">int</span> <span class="p">(</span><span class="n">cloud_cluster_don</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">());</span>
    <span class="n">cloud_cluster_don</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
    <span class="n">cloud_cluster_don</span><span class="o">-&gt;</span><span class="n">is_dense</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>

    <span class="c1">//Save cluster</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud representing the Cluster: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_cluster_don</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;don_cluster_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">j</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;.pcd&quot;</span><span class="p">;</span>
    <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="o">*</span><span class="n">cloud_cluster_don</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<div class="section" id="compiling-and-running-the-program">
<h3><a class="toc-backref" href="#id12">Compiling and running the program</a></h3>
<p>Add the following lines to your CMakeLists.txt file:</p>
<div class="highlight-cmake notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">don_segmentation</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.7</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">don_segmentation</span> <span class="s">don_segmentation.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">don_segmentation</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>Create a build directory, and build the executable:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ mkdir build
$ cd build
$ cmake ..
$ make
</pre></div>
</div>
<p>After you have made the executable, you can run it. Simply run:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ./don_segmentation &lt;inputfile&gt; &lt;smallscale&gt; &lt;largescale&gt; &lt;threshold&gt; &lt;segradius&gt;
</pre></div>
</div>
</div>
</div>
<div class="section" id="the-explanation">
<h2><a class="toc-backref" href="#id13">The Explanation</a></h2>
<div class="section" id="large-small-radius-normal-estimation">
<h3><a class="toc-backref" href="#id14">Large/Small Radius Normal Estimation</a></h3>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Create a search tree, use KDTreee for non-organized data.</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">Search</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">isOrganized</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">tree</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">OrganizedNeighbor</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">tree</span><span class="p">.</span><span class="n">reset</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="nb">false</span><span class="p">));</span>
  <span class="p">}</span>

  <span class="c1">// Set the input pointcloud for the search tree</span>
  <span class="n">tree</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
</pre></div>
</div>
<p>We will skip the code for loading files and parsing command line arguments, and go straight to the first major PCL calls. For our later calls to calculate normals, we need to create a search tree. For organized data (i.e. a depth image), a much faster search tree is the OrganizedNeighbor search tree. For unorganized data, i.e. LiDAR scans, a KDTree is a good option.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Compute normals using both small and large scales at each point</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimationOMP</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="cm">/**</span>
<span class="cm">   * NOTE: setting viewpoint is very important, so that we can ensure</span>
<span class="cm">   * normals are all pointed in the same direction!</span>
<span class="cm">   */</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setViewPoint</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">max</span> <span class="p">(),</span> <span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">max</span> <span class="p">(),</span> <span class="n">std</span><span class="o">::</span><span class="n">numeric_limits</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">&gt;::</span><span class="n">max</span> <span class="p">());</span>
</pre></div>
</div>
<p>This is perhaps the most important section of code, estimating the normals. This is also the bottleneck computationally, and so we will use the <code class="docutils literal notranslate"><span class="pre">pcl::NormalEstimationOMP</span></code> class which makes use of OpenMP to use many threads to calculate the normal using the multiple cores found in most modern processors.  We could also use the standard single-threaded class <code class="docutils literal notranslate"><span class="pre">pcl::NormalEstimation</span></code>, or even the GPU accelerated class <code class="docutils literal notranslate"><span class="pre">pcl::gpu::NormalEstimation</span></code>. Whatever class we use, it is important to set an arbitrary viewpoint to be used across all the normal calculations - this ensures that normals estimated at different scales share a consistent orientation.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>For information and examples on estimating normals, normal ambiguity, and the different normal estimation methods in PCL, please read the <a class="reference internal" href="normal_estimation.php#normal-estimation"><span class="std std-ref">Estimating Surface Normals in a PointCloud</span></a> tutorial.</p>
</div>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// calculate normals with the small scale</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Calculating normals for scale...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scale1</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals_small_scale</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">scale1</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">normals_small_scale</span><span class="p">);</span>

  <span class="c1">// calculate normals with the large scale</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Calculating normals for scale...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">scale2</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals_large_scale</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="n">scale2</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">normals_large_scale</span><span class="p">);</span>
</pre></div>
</div>
<p>Next we calculate the normals using our normal estimation class for both the large and small radius. It is important to use the <code class="docutils literal notranslate"><span class="pre">NormalEstimation.setRadiusSearch()</span></code> method v.s. the <code class="docutils literal notranslate"><span class="pre">NormalEstimation.setMaximumNeighbours()</span></code> method or equivalent. If the normal estimate is restricted to a set number of neighbours, it may not be based on the complete surface of the given radius, and thus is not suitable for the Difference of Normals features.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>For large supporting radii in dense point clouds, calculating the normal would be a very computationally intensive task potentially utilizing thousands of points in the calculation, when hundreds are more than enough for an accurate estimate. A simple method to speed up the calculation is to uniformly subsample the pointcloud when doing a large radius search, see the full example code in the PCL distribution at <code class="docutils literal notranslate"><span class="pre">examples/features/example_difference_of_normals.cpp</span></code> for more details.</p>
</div>
</div>
<div class="section" id="difference-of-normals-feature-calculation">
<h3><a class="toc-backref" href="#id15">Difference of Normals Feature Calculation</a></h3>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Create output cloud for DoN results</span>
  <span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">doncloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">copyPointCloud</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud</span><span class="p">,</span> <span class="o">*</span><span class="n">doncloud</span><span class="p">);</span>
</pre></div>
</div>
<p>We can now perform the actual Difference of Normals feature calculation using our normal estimates. The Difference of Normals result is a vector field, so we initialize the point cloud to store the results in as a <code class="docutils literal notranslate"><span class="pre">pcl::PointNormal</span></code> point cloud, and copy the points from our input pointcloud over to it, so we have what may be regarded as an uninitialized vector field for our point cloud.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Create DoN operator</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">DifferenceOfNormalsEstimation</span><span class="o">&lt;</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">PointNormal</span><span class="p">,</span> <span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">don</span><span class="p">;</span>
  <span class="n">don</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">don</span><span class="p">.</span><span class="n">setNormalScaleLarge</span> <span class="p">(</span><span class="n">normals_large_scale</span><span class="p">);</span>
  <span class="n">don</span><span class="p">.</span><span class="n">setNormalScaleSmall</span> <span class="p">(</span><span class="n">normals_small_scale</span><span class="p">);</span>

  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">don</span><span class="p">.</span><span class="n">initCompute</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cerr</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Error: Could not initialize DoN feature operator&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">exit</span> <span class="p">(</span><span class="n">EXIT_FAILURE</span><span class="p">);</span>
  <span class="p">}</span>

  <span class="c1">// Compute DoN</span>
  <span class="n">don</span><span class="p">.</span><span class="n">computeFeature</span> <span class="p">(</span><span class="o">*</span><span class="n">doncloud</span><span class="p">);</span>
</pre></div>
</div>
<p>We instantiate a new <code class="docutils literal notranslate"><span class="pre">pcl::DifferenceOfNormalsEstimation</span></code> class to take care of calculating the Difference of Normals vector field.</p>
<p>The <code class="docutils literal notranslate"><span class="pre">pcl::DifferenceOfNormalsEstimation</span></code> class has 3 template parameters, the first corresponds to the input point cloud type, in this case <code class="docutils literal notranslate"><span class="pre">pcl::PointXYZRGB</span></code>, the second corresponds to the type of the normals estimated for the point cloud, in this case <code class="docutils literal notranslate"><span class="pre">pcl::PointNormal</span></code>, and the third corresponds to the vector field output type, in this case also <code class="docutils literal notranslate"><span class="pre">pcl::PointNormal</span></code>. Next we set the input point cloud and give both of the normals estimated for the point cloud, and check that the requirements for computing the features are satisfied using the <code class="docutils literal notranslate"><span class="pre">pcl::DifferenceOfNormalsEstimation::initCompute()</span></code> method. Finally we compute the features by calling the <code class="docutils literal notranslate"><span class="pre">pcl::DifferenceOfNormalsEstimation::computeFeature()</span></code> method.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>The <code class="docutils literal notranslate"><span class="pre">pcl::DifferenceOfNormalsEstimation</span></code> class expects the given point cloud and normal point clouds indices to match, i.e. the first point in the input point cloud’s normals should also be the first point in the two normal point clouds.</p>
</div>
</div>
<div class="section" id="difference-of-normals-based-filtering">
<h3><a class="toc-backref" href="#id16">Difference of Normals Based Filtering</a></h3>
<p>While we now have a Difference of Normals vector field, we still have the complete point set. To begin the segmentation process, we must actually discriminate points based on their Difference of Normals vector result. There are a number of common quantities you may want to try filtering by:</p>
<table class="docutils align-default">
<colgroup>
<col style="width: 40%" />
<col style="width: 13%" />
<col style="width: 14%" />
<col style="width: 33%" />
</colgroup>
<thead>
<tr class="row-odd"><th class="head"><p>Quantity</p></th>
<th class="head"><p>PointNormal Field</p></th>
<th class="head"><p>Description</p></th>
<th class="head"><p>Usage Scenario</p></th>
</tr>
</thead>
<tbody>
<tr class="row-even"><td><p><img class="math" src="_images/math/14edaa047f6c69bd6f3d838864eb2225109e5fd7.png" alt="\mathbf{\Delta}\mathbf{\hat{n}}(p, r_s, r_l)"/></p></td>
<td><p>float normal[3]</p></td>
<td><p>DoN vector</p></td>
<td><p>Filtering points by relative DoN angle.</p></td>
</tr>
<tr class="row-odd"><td><p><img class="math" src="_images/math/ceab5d827460b878f31839ea5b48e5aa36c16fdd.png" alt="|\mathbf{\Delta}\mathbf{\hat{n}}(p, r_s, r_l)| \in (0,1)"/></p></td>
<td><p>float curvature</p></td>
<td><p>DoN <img class="math" src="_images/math/ad2c138fe32aaa0450c345ae449948865ab94865.png" alt="l_2"/> norm</p></td>
<td><p>Filtering points by scale membership, large magnitude
indicates point has a strong response at then given
scale parameters</p></td>
</tr>
<tr class="row-even"><td><p><img class="math" src="_images/math/2926b6c4ed5560a2b11284b0b46cc9372e9a9f5a.png" alt="\mathbf{\Delta}\mathbf{\hat{n}}(p, r_s, r_l)_x \in (-1,1)"/>,</p></td>
<td><p>float normal[0]</p></td>
<td><p>DoN vector x component</p></td>
<td rowspan="3"><p>Filtering points by orientable scale, i.e. building
facades with large
large <img class="math" src="_images/math/6dc336e962f4fa4ff6c62c04aa464413b3d3bfd8.png" alt="|{\mathbf{\Delta}\mathbf{\hat{n}}}_x|"/>
and/or <img class="math" src="_images/math/9fde4f4d1a2bdbb02974239b4225931396375250.png" alt="|{\mathbf{\Delta}\mathbf{\hat{n}}}_y|"/> and
small <img class="math" src="_images/math/376ecbf379a469bb046dea170a6c0d6fc8bd3367.png" alt="|{\mathbf{\Delta}\mathbf{\hat{n}}}_z|"/></p></td>
</tr>
<tr class="row-odd"><td><p><img class="math" src="_images/math/8fa079ebceb2959adc65371ef9f8a3b3751ec0ae.png" alt="\mathbf{\Delta}\mathbf{\hat{n}}(p, r_s, r_l)_y \in (-1,1)"/>,</p></td>
<td><p>float normal[1]</p></td>
<td><p>DoN vector y component</p></td>
</tr>
<tr class="row-even"><td><p><img class="math" src="_images/math/e949c06de9e79da541f4aed3107f7beef85ccfd2.png" alt="\mathbf{\Delta}\mathbf{\hat{n}}(p, r_s, r_l)_z \in (-1,1)"/>,</p></td>
<td><p>float normal[2]</p></td>
<td><p>DoN vector z component</p></td>
</tr>
</tbody>
</table>
<p>In this example we will do a simple magnitude threshold, looking for objects of a scale regardless of their orientation in the scene. To do so, we must create a conditional filter:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Build the condition for filtering</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionOr</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">range_cond</span> <span class="p">(</span>
    <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionOr</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="p">()</span>
    <span class="p">);</span>
  <span class="n">range_cond</span><span class="o">-&gt;</span><span class="n">addComparison</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="p">(</span>
                               <span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">FieldComparison</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="p">(</span><span class="s">&quot;curvature&quot;</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ComparisonOps</span><span class="o">::</span><span class="n">GT</span><span class="p">,</span> <span class="n">threshold</span><span class="p">))</span>
                             <span class="p">);</span>
  <span class="c1">// Build the filter</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ConditionalRemoval</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">condrem</span><span class="p">;</span>
  <span class="n">condrem</span><span class="p">.</span><span class="n">setCondition</span> <span class="p">(</span><span class="n">range_cond</span><span class="p">);</span>
  <span class="n">condrem</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">doncloud</span><span class="p">);</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">doncloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Apply filter</span>
  <span class="n">condrem</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">doncloud_filtered</span><span class="p">);</span>
</pre></div>
</div>
<p>After we apply the filter we are left with a reduced pointcloud consisting of the points with a strong response with the given scale parameters.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>For more information on point cloud filtering and building filtering conditions, please read the <a class="reference internal" href="conditional_removal.php#conditional-removal"><span class="std std-ref">Removing outliers using a ConditionalRemoval filter</span></a> tutorial.</p>
</div>
</div>
<div class="section" id="clustering-the-results">
<h3><a class="toc-backref" href="#id17">Clustering the Results</a></h3>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Filter by magnitude</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Clustering using EuclideanClusterExtraction with tolerance &lt;= &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">segradius</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;...&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">segtree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">segtree</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">doncloud</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="n">cluster_indices</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">EuclideanClusterExtraction</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="n">ec</span><span class="p">;</span>

  <span class="n">ec</span><span class="p">.</span><span class="n">setClusterTolerance</span> <span class="p">(</span><span class="n">segradius</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setMinClusterSize</span> <span class="p">(</span><span class="mi">50</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setMaxClusterSize</span> <span class="p">(</span><span class="mi">100000</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">segtree</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">doncloud</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">cluster_indices</span><span class="p">);</span>
</pre></div>
</div>
<p>Finally, we are usually left with a number of objects or regions with good isolation, allowing us to use a simple clustering algorithm to segment the results. In this example we used Euclidean Clustering with a threshold equal to the small radius parameter.</p>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>For more information on point cloud clustering, please read the <a class="reference internal" href="cluster_extraction.php#cluster-extraction"><span class="std std-ref">Euclidean Cluster Extraction</span></a> tutorial.</p>
</div>
<p>After the segmentation the cloud viewer window will be opened and you will see something similar to those images:</p>
<a class="reference internal image-reference" href="_images/don_clusters.jpg"><img alt="_images/don_clusters.jpg" src="_images/don_clusters.jpg" style="height: 360px;" /></a>
</div>
<div class="section" id="references-further-information">
<h3><a class="toc-backref" href="#id18">References/Further Information</a></h3>
<dl class="citation">
<dt class="label" id="don2012"><span class="brackets"><a class="fn-backref" href="#id1">DON2012</a></span></dt>
<dd><p>“Difference of Normals as a Multi-Scale Operator in Unorganized Point Clouds” &lt;<a class="reference external" href="http://arxiv.org/abs/1209.1759">http://arxiv.org/abs/1209.1759</a>&gt;.</p>
</dd>
</dl>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>&#64;ARTICLE{2012arXiv1209.1759I,
author = {{Ioannou}, Y. and {Taati}, B. and {Harrap}, R. and {Greenspan}, M.},
title = “{Difference of Normals as a Multi-Scale Operator in Unorganized Point Clouds}”,
journal = {ArXiv e-prints},
archivePrefix = “arXiv”,
eprint = {1209.1759},
primaryClass = “cs.CV”,
keywords = {Computer Science - Computer Vision and Pattern Recognition},
year = 2012,
month = sep,
}</p>
</div>
<dl class="citation">
<dt class="label" id="kitti"><span class="brackets"><a class="fn-backref" href="#id2">KITTI</a></span></dt>
<dd><p>“The KITTI Vision Benchmark Suite” &lt;<a class="reference external" href="http://www.cvlibs.net/datasets/kitti/">http://www.cvlibs.net/datasets/kitti/</a>&gt;.</p>
</dd>
<dt class="label" id="kittipcl"><span class="brackets"><a class="fn-backref" href="#id3">KITTIPCL</a></span></dt>
<dd><p>“KITTI PCL Toolkit” &lt;<a class="reference external" href="https://github.com/yanii/kitti-pcl">https://github.com/yanii/kitti-pcl</a>&gt;</p>
</dd>
</dl>
</div>
</div>
</div>


          </div>
      </div>
      <div class="clearer"></div>
    </div>
</div> <!-- #page-content -->

<?php
$chunkOutput = $modx->getChunk("site-footer");
echo $chunkOutput;
?>

  </body>
</html>