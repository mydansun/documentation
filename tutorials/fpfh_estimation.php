<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Fast Point Feature Histograms (FPFH) descriptors &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="fast-point-feature-histograms-fpfh-descriptors">
<span id="fpfh-estimation"></span><h1>Fast Point Feature Histograms (FPFH) descriptors</h1>
<p>The theoretical computational complexity of the Point Feature Histogram (see
<a class="reference internal" href="pfh_estimation.php#pfh-estimation"><span class="std std-ref">Point Feature Histograms (PFH) descriptors</span></a>) for a given point cloud <img class="math" src="_images/math/c2aa3dff9bffb099e9dff196fd36aed56ec16baf.png" alt="P"/> with <img class="math" src="_images/math/5a939c5280da7202ca4531f175a7780ad5e1f80a.png" alt="n"/> points
is <img class="math" src="_images/math/5d514ad222768df51f9af829e272973767c9157f.png" alt="O(nk^2)"/>, where <img class="math" src="_images/math/9630132210b904754c9ab272b61cb527d12263ca.png" alt="k"/> is the number of neighbors for each point
<img class="math" src="_images/math/141bbefb74014fc5e43499901bf78607ae335583.png" alt="p"/> in <img class="math" src="_images/math/c2aa3dff9bffb099e9dff196fd36aed56ec16baf.png" alt="P"/>. For real-time or near real-time applications, the
computation of Point Feature Histograms in dense point neighborhoods can
represent one of the major bottlenecks.</p>
<p>This tutorial describes a simplification of the PFH formulation, called Fast
Point Feature Histograms (FPFH) (see <a class="reference internal" href="how_features_work.php#rusudissertation" id="id1"><span>[RusuDissertation]</span></a> for more information),
that reduces the computational complexity of the algorithm to <img class="math" src="_images/math/aa00ebb0ca3a2a27d8ec020736b494c67518c83d.png" alt="O(nk)"/>,
while still retaining most of the discriminative power of the PFH.</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical primer</h1>
<p>To simplify the histogram feature computation, we proceed as follows:</p>
<blockquote>
<div><ul class="simple">
<li><p>in a first step, for each query point <img class="math" src="_images/math/2fa9878d2c9dd8bc006ba9d7986fab0030ed8452.png" alt="p_q"/> a set of tuples
<img class="math" src="_images/math/76d82af38f719dd8f5987e4e500326ef249dd471.png" alt="\alpha, \phi, \theta"/> between itself and its neighbors are computed
as described in <a class="reference internal" href="pfh_estimation.php#pfh-estimation"><span class="std std-ref">Point Feature Histograms (PFH) descriptors</span></a> - this will be called the Simplified
Point Feature Histogram (SPFH);</p></li>
<li><p>in a second step, for each point its k neighbors are re-determined, and the
neighboring SPFH values are used to weight the final histogram of pq
(called FPFH) as follows:</p></li>
</ul>
</div></blockquote>
<div class="math">
<p><img src="_images/math/d4aee1a5151c243dc06e0ffb3f498d33b4be340e.png" alt="FPFH(\boldsymbol{p}_q) = SPFH(\boldsymbol{p}_q) + {1 \over k} \sum_{i=1}^k {{1 \over \omega_k} \cdot SPFH(\boldsymbol{p}_k)}"/></p>
</div><p>where the weight <img class="math" src="_images/math/6e38baa19ecd870ddee5bb9b3074b65ae5614d88.png" alt="\omega_k"/> represents a distance between the query point
<img class="math" src="_images/math/2fa9878d2c9dd8bc006ba9d7986fab0030ed8452.png" alt="p_q"/> and a neighbor point <img class="math" src="_images/math/61b27bc840bb5922cebcdbabfe06610d6c5ad129.png" alt="p_k"/> in some given metric space, thus
scoring the (<img class="math" src="_images/math/f1526373cdef8559a8b65902a15ecf7688a78be8.png" alt="p_q, p_k"/>) pair, but could just as well be selected as a
different measure if necessary.  To understand the importance of this weighting
scheme, the figure below presents the influence region diagram for a
k-neighborhood set centered at <img class="math" src="_images/math/2fa9878d2c9dd8bc006ba9d7986fab0030ed8452.png" alt="p_q"/>.</p>
<img alt="_images/fpfh_diagram.png" class="align-center" src="_images/fpfh_diagram.png" />
<p>Thus, for a given query point <img class="math" src="_images/math/2fa9878d2c9dd8bc006ba9d7986fab0030ed8452.png" alt="p_q"/>, the algorithm first estimates its
SPFH values by creating pairs between itself and its neighbors (illustrated
using red lines). This is repeated for all the points in the dataset, followed
by a re-weighting of the SPFH values of pq using the SPFH values of its
<img class="math" src="_images/math/61b27bc840bb5922cebcdbabfe06610d6c5ad129.png" alt="p_k"/> neighbors, thus creating the FPFH for <img class="math" src="_images/math/2fa9878d2c9dd8bc006ba9d7986fab0030ed8452.png" alt="p_q"/>. The extra FPFH
connections, resultant due to the additional weighting scheme, are shown with
black lines. As the diagram shows, some of the value pairs will be counted
twice (marked with thicker lines in the figure).</p>
</div>
<div class="section" id="differences-between-pfh-and-fpfh">
<h1>Differences between PFH and FPFH</h1>
<p>The main differences between the PFH and FPFH formulations are summarized below:</p>
<blockquote>
<div><ol class="arabic simple">
<li><p>the FPFH does not fully interconnect all neighbors of <img class="math" src="_images/math/2fa9878d2c9dd8bc006ba9d7986fab0030ed8452.png" alt="p_q"/> as it
can be seen from the figure, and is thus missing some value pairs which
might contribute to capture the geometry around the query point;</p></li>
<li><p>the PFH models a precisely determined surface around the query point,
while the FPFH includes additional point pairs outside the <strong>r</strong> radius
sphere (though at most <strong>2r</strong> away);</p></li>
<li><p>because of the re-weighting scheme, the FPFH combines SPFH values and
recaptures some of the point neighboring value pairs;</p></li>
<li><p>the overall complexity of FPFH is greatly reduced, thus making possible to
use it in real-time applications;</p></li>
<li><p>the resultant histogram is simplified by decorrelating the values, that is
simply creating <em>d</em> separate feature histograms, one for each feature
dimension, and concatenate them together (see figure below).</p></li>
</ol>
</div></blockquote>
<img alt="_images/fpfh_theory.jpg" class="align-center" src="_images/fpfh_theory.jpg" />
</div>
<div class="section" id="estimating-fpfh-features">
<h1>Estimating FPFH features</h1>
<p>Fast Point Feature Histograms are implemented in PCL as part of the
<a class="reference external" href="http://docs.pointclouds.org/trunk/a02944.html">pcl_features</a>
library.</p>
<p>The default FPFH implementation uses 11 binning subdivisions (e.g., each of the
four feature values will use this many bins from its value interval), and a
decorrelated scheme (see above: the feature histograms are computed separately
and concantenated) which results in a 33-byte array of float values. These are
stored in a <strong>pcl::FPFHSignature33</strong> point type.</p>
<p>The following code snippet will estimate a set of FPFH features for all the
points in the input dataset.</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
34</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/fpfh.h&gt;</span><span class="cp"></span>

<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="p">...</span> <span class="n">read</span><span class="p">,</span> <span class="n">pass</span> <span class="n">in</span> <span class="n">or</span> <span class="n">create</span> <span class="n">a</span> <span class="n">point</span> <span class="n">cloud</span> <span class="n">with</span> <span class="n">normals</span> <span class="p">...</span>
  <span class="p">...</span> <span class="p">(</span><span class="nl">note</span><span class="p">:</span> <span class="n">you</span> <span class="n">can</span> <span class="n">create</span> <span class="n">a</span> <span class="n">single</span> <span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointNormal</span><span class="o">&gt;</span> <span class="k">if</span> <span class="n">you</span> <span class="n">want</span><span class="p">)</span> <span class="p">...</span>

  <span class="c1">// Create the FPFH estimation class, and pass the input dataset+normals to it</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">FPFHSignature33</span><span class="o">&gt;</span> <span class="n">fpfh</span><span class="p">;</span>
  <span class="n">fpfh</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">fpfh</span><span class="p">.</span><span class="n">setInputNormals</span> <span class="p">(</span><span class="n">normals</span><span class="p">);</span>
  <span class="c1">// alternatively, if cloud is of tpe PointNormal, do fpfh.setInputNormals (cloud);</span>

  <span class="c1">// Create an empty kdtree representation, and pass it to the FPFH estimation object.</span>
  <span class="c1">// Its content will be filled inside the object, based on the given input dataset (as no other search surface is given).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="n">fpfh</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="c1">// Output datasets</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">FPFHSignature33</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">fpfhs</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">FPFHSignature33</span><span class="o">&gt;</span> <span class="p">());</span>

  <span class="c1">// Use all neighbors in a sphere of radius 5cm</span>
  <span class="c1">// IMPORTANT: the radius used here has to be larger than the radius used to estimate the surface normals!!!</span>
  <span class="n">fpfh</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">);</span>

  <span class="c1">// Compute the features</span>
  <span class="n">fpfh</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">fpfhs</span><span class="p">);</span>

  <span class="c1">// fpfhs-&gt;points.size () should have the same size as the input cloud-&gt;points.size ()*</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>The actual <strong>compute</strong> call from the <strong>FPFHEstimation</strong> class does nothing internally but:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>for each point p in cloud P

  1. pass 1:

     1. get the nearest neighbors of :math:`p`

     2. for each pair of :math:`p, p_k` (where :math:`p_k` is a neighbor of :math:`p`, compute the three angular values

     3. bin all the results in an output SPFH histogram

  2. pass 2:

     1. get the nearest neighbors of :math:`p`

     3. use each SPFH of :math:`p` with a weighting scheme to assemble the FPFH of :math:`p`:
</pre></div>
</div>
<div class="admonition note">
<p class="admonition-title">Note</p>
<p>For efficiency reasons, the <strong>compute</strong> method in <strong>FPFHEstimation</strong> does not check if the normals contains NaN or infinite values.
Passing such values to <strong>compute()</strong> will result in undefined output.
It is advisable to check the normals, at least during the design of the processing chain or when setting the parameters.
This can be done by inserting the following code before the call to <strong>compute()</strong>:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="k">for</span> <span class="p">(</span><span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">normals</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span><span class="p">();</span> <span class="n">i</span><span class="o">++</span><span class="p">)</span>
<span class="p">{</span>
  <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="n">pcl</span><span class="o">::</span><span class="n">isFinite</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">(</span><span class="n">normals</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">i</span><span class="p">]))</span>
  <span class="p">{</span>
    <span class="n">PCL_WARN</span><span class="p">(</span><span class="s">&quot;normals[%d] is not finite</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">,</span> <span class="n">i</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</div>
<p>In production code, preprocessing steps and parameters should be set so that normals are finite or raise an error.</p>
</div>
</div>
<div class="section" id="speeding-fpfh-with-openmp">
<h1>Speeding FPFH with OpenMP</h1>
<p>For the speed-savvy users, PCL provides an additional implementation of FPFH
estimation which uses multi-core/multi-threaded paradigms using OpenMP to speed
the computation. The name of the class is <strong>pcl::FPFHEstimationOMP</strong>, and its
API is 100% compatible to the single-threaded <strong>pcl::FPFHEstimation</strong>, which
makes it suitable as a drop-in replacement. On a system with 8 cores, you
should get anything between 6-8 times faster computation times.</p>
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