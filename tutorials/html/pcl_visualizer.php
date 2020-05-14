<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <title>PCLVisualizer</title>
    
    <link rel="stylesheet" href="_static/sphinxdoc.css" type="text/css" />
    <link rel="stylesheet" href="_static/pygments.css" type="text/css" />
    
    <script type="text/javascript">
      var DOCUMENTATION_OPTIONS = {
        URL_ROOT:    './',
        VERSION:     '0.0',
        COLLAPSE_INDEX: false,
        FILE_SUFFIX: '.php',
        HAS_SOURCE:  true
      };
    </script>
    <script type="text/javascript" src="_static/jquery.js"></script>
    <script type="text/javascript" src="_static/underscore.js"></script>
    <script type="text/javascript" src="_static/doctools.js"></script>
    <link rel="top" title="None" href="index.php" />
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

  </head>
  <body>

    <div class="document">
      <div class="documentwrapper">
          <div class="body">
            
  <div class="section" id="pclvisualizer">
<span id="pcl-visualizer"></span><h1>PCLVisualizer</h1>
<p>PCLVisualizer is PCL&#8217;s full-featured visualisation class. While more
complex to use than the CloudViewer, it is also more powerful, offering
features such as displaying normals, drawing shapes and multiple
viewports.</p>
<p>This tutorial will use a code sample to illustrate some of the features
of PCLVisualizer, beginning with displaying a single point cloud. Most
of the code sample is boilerplate to set up the point clouds that will
be visualised. The relevant code for each sample is contained in a
function specific to that sample. The code is shown below. Copy it into
a file named <tt class="docutils literal"><span class="pre">pcl_visualizer_demo.cpp</span></tt>.</p>
<div class="highlight-cpp"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
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
190
191
192
193
194
195
196
197
198
199
200
201
202
203
204
205
206
207
208
209
210
211
212
213
214
215
216
217
218
219
220
221
222
223
224
225
226
227
228
229
230
231
232
233
234
235
236
237
238
239
240
241
242
243
244
245
246
247
248
249
250
251
252
253
254
255
256
257
258
259
260
261
262
263
264
265
266
267
268
269
270
271
272
273
274
275
276
277
278
279
280
281
282
283
284
285
286
287
288
289
290
291
292
293
294
295
296
297
298
299
300
301
302
303
304
305
306
307
308
309
310
311
312
313
314
315
316
317
318
319
320
321
322
323
324
325
326
327
328
329
330
331
332
333
334
335
336
337
338
339
340
341
342
343
344
345
346
347
348
349
350
351
352
353
354
355
356
357
358
359
360
361
362
363
364
365
366
367
368
369
370
371
372
373
374
375
376
377
378
379
380</pre></div></td><td class="code"><div class="highlight"><pre><span class="cm">/* \author Geoffrey Biggs */</span>


<span class="cp">#include &lt;iostream&gt;</span>

<span class="cp">#include &lt;boost/thread/thread.hpp&gt;</span>
<span class="cp">#include &lt;pcl/common/common_headers.h&gt;</span>
<span class="cp">#include &lt;pcl/features/normal_3d.h&gt;</span>
<span class="cp">#include &lt;pcl/io/pcd_io.h&gt;</span>
<span class="cp">#include &lt;pcl/visualization/pcl_visualizer.h&gt;</span>
<span class="cp">#include &lt;pcl/console/parse.h&gt;</span>

<span class="c1">// --------------</span>
<span class="c1">// -----Help-----</span>
<span class="c1">// --------------</span>
<span class="kt">void</span>
<span class="nf">printUsage</span> <span class="p">(</span><span class="k">const</span> <span class="kt">char</span><span class="o">*</span> <span class="n">progName</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n\n</span><span class="s">Usage: &quot;</span><span class="o">&lt;&lt;</span><span class="n">progName</span><span class="o">&lt;&lt;</span><span class="s">&quot; [options]</span><span class="se">\n\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;Options:</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-------------------------------------------</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-h           this help</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-s           Simple visualisation example</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-r           RGB colour visualisation example</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-c           Custom colour visualisation example</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-n           Normals visualisation example</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-a           Shapes visualisation example</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-v           Viewports example</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;-i           Interaction Customization example</span><span class="se">\n</span><span class="s">&quot;</span>
            <span class="o">&lt;&lt;</span> <span class="s">&quot;</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">;</span>
<span class="p">}</span>


<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">simpleVis</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="c1">// -----Open 3D viewer and add point cloud-----</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">}</span>


<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">rgbVis</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="c1">// -----Open 3D viewer and add point cloud-----</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">}</span>


<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">customColourVis</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="c1">// -----Open 3D viewer and add point cloud-----</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">single_color</span><span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">single_color</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">}</span>


<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">normalsVis</span> <span class="p">(</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">normals</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------------------------</span>
  <span class="c1">// -----Open 3D viewer and add point cloud and normals-----</span>
  <span class="c1">// --------------------------------------------------------</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloudNormals</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">normals</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="s">&quot;normals&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
  <span class="k">return</span> <span class="p">(</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">}</span>


<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">shapesVis</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="c1">// -----Open 3D viewer and add point cloud-----</span>
  <span class="c1">// --------------------------------------------</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>

  <span class="c1">//------------------------------------</span>
  <span class="c1">//-----Add shapes at cloud points-----</span>
  <span class="c1">//------------------------------------</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addLine</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span>
                                     <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">size</span><span class="p">()</span> <span class="o">-</span> <span class="mi">1</span><span class="p">],</span> <span class="s">&quot;line&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addSphere</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="mf">0.2</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="s">&quot;sphere&quot;</span><span class="p">);</span>

  <span class="c1">//---------------------------------------</span>
  <span class="c1">//-----Add shapes at other locations-----</span>
  <span class="c1">//---------------------------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span> <span class="n">coeffs</span><span class="p">;</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPlane</span> <span class="p">(</span><span class="n">coeffs</span><span class="p">,</span> <span class="s">&quot;plane&quot;</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">clear</span> <span class="p">();</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">0.3</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">0.3</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
  <span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="mf">5.0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCone</span> <span class="p">(</span><span class="n">coeffs</span><span class="p">,</span> <span class="s">&quot;cone&quot;</span><span class="p">);</span>

  <span class="k">return</span> <span class="p">(</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">}</span>


<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewportsVis</span> <span class="p">(</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">normals1</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">normals2</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------------------------</span>
  <span class="c1">// -----Open 3D viewer and add point cloud and normals-----</span>
  <span class="c1">// --------------------------------------------------------</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>

  <span class="kt">int</span> <span class="nf">v1</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">createViewPort</span><span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addText</span><span class="p">(</span><span class="s">&quot;Radius: 0.01&quot;</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;v1 text&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;sample cloud1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>

  <span class="kt">int</span> <span class="nf">v2</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">createViewPort</span><span class="p">(</span><span class="mf">0.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mf">0.3</span><span class="p">,</span> <span class="mf">0.3</span><span class="p">,</span> <span class="mf">0.3</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addText</span><span class="p">(</span><span class="s">&quot;Radius: 0.1&quot;</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;v2 text&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">single_color</span><span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">single_color</span><span class="p">,</span> <span class="s">&quot;sample cloud2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud1&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud2&quot;</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>

  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloudNormals</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">normals1</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="s">&quot;normals1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloudNormals</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">normals2</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="s">&quot;normals2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>

  <span class="k">return</span> <span class="p">(</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">}</span>


<span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">text_id</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
<span class="kt">void</span> <span class="nf">keyboardEventOccurred</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">KeyboardEvent</span> <span class="o">&amp;</span><span class="n">event</span><span class="p">,</span>
                            <span class="kt">void</span><span class="o">*</span> <span class="n">viewer_void</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="o">=</span> <span class="o">*</span><span class="k">static_cast</span><span class="o">&lt;</span><span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="o">*&gt;</span> <span class="p">(</span><span class="n">viewer_void</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">event</span><span class="p">.</span><span class="n">getKeySym</span> <span class="p">()</span> <span class="o">==</span> <span class="s">&quot;r&quot;</span> <span class="o">&amp;&amp;</span> <span class="n">event</span><span class="p">.</span><span class="n">keyDown</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;r was pressed =&gt; removing all text&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

    <span class="kt">char</span> <span class="n">str</span><span class="p">[</span><span class="mi">512</span><span class="p">];</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">text_id</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">sprintf</span> <span class="p">(</span><span class="n">str</span><span class="p">,</span> <span class="s">&quot;text#%03d&quot;</span><span class="p">,</span> <span class="n">i</span><span class="p">);</span>
      <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">removeShape</span> <span class="p">(</span><span class="n">str</span><span class="p">);</span>
    <span class="p">}</span>
    <span class="n">text_id</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">}</span>

<span class="kt">void</span> <span class="nf">mouseEventOccurred</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">MouseEvent</span> <span class="o">&amp;</span><span class="n">event</span><span class="p">,</span>
                         <span class="kt">void</span><span class="o">*</span> <span class="n">viewer_void</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="o">=</span> <span class="o">*</span><span class="k">static_cast</span><span class="o">&lt;</span><span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="o">*&gt;</span> <span class="p">(</span><span class="n">viewer_void</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">event</span><span class="p">.</span><span class="n">getButton</span> <span class="p">()</span> <span class="o">==</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">MouseEvent</span><span class="o">::</span><span class="n">LeftButton</span> <span class="o">&amp;&amp;</span>
      <span class="n">event</span><span class="p">.</span><span class="n">getType</span> <span class="p">()</span> <span class="o">==</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">MouseEvent</span><span class="o">::</span><span class="n">MouseButtonRelease</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Left mouse button released at position (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">event</span><span class="p">.</span><span class="n">getX</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;, &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">event</span><span class="p">.</span><span class="n">getY</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

    <span class="kt">char</span> <span class="n">str</span><span class="p">[</span><span class="mi">512</span><span class="p">];</span>
    <span class="n">sprintf</span> <span class="p">(</span><span class="n">str</span><span class="p">,</span> <span class="s">&quot;text#%03d&quot;</span><span class="p">,</span> <span class="n">text_id</span> <span class="o">++</span><span class="p">);</span>
    <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addText</span> <span class="p">(</span><span class="s">&quot;clicked here&quot;</span><span class="p">,</span> <span class="n">event</span><span class="p">.</span><span class="n">getX</span> <span class="p">(),</span> <span class="n">event</span><span class="p">.</span><span class="n">getY</span> <span class="p">(),</span> <span class="n">str</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>

<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">interactionCustomizationVis</span> <span class="p">()</span>
<span class="p">{</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>

  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">registerKeyboardCallback</span> <span class="p">(</span><span class="n">keyboardEventOccurred</span><span class="p">,</span> <span class="p">(</span><span class="kt">void</span><span class="o">*</span><span class="p">)</span><span class="o">&amp;</span><span class="n">viewer</span><span class="p">);</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">registerMouseCallback</span> <span class="p">(</span><span class="n">mouseEventOccurred</span><span class="p">,</span> <span class="p">(</span><span class="kt">void</span><span class="o">*</span><span class="p">)</span><span class="o">&amp;</span><span class="n">viewer</span><span class="p">);</span>

  <span class="k">return</span> <span class="p">(</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">}</span>


<span class="c1">// --------------</span>
<span class="c1">// -----Main-----</span>
<span class="c1">// --------------</span>
<span class="kt">int</span>
<span class="n">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// --------------------------------------</span>
  <span class="c1">// -----Parse Command Line Arguments-----</span>
  <span class="c1">// --------------------------------------</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-h&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">printUsage</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="kt">bool</span> <span class="n">simple</span><span class="p">(</span><span class="nb">false</span><span class="p">),</span> <span class="n">rgb</span><span class="p">(</span><span class="nb">false</span><span class="p">),</span> <span class="n">custom_c</span><span class="p">(</span><span class="nb">false</span><span class="p">),</span> <span class="n">normals</span><span class="p">(</span><span class="nb">false</span><span class="p">),</span>
    <span class="n">shapes</span><span class="p">(</span><span class="nb">false</span><span class="p">),</span> <span class="n">viewports</span><span class="p">(</span><span class="nb">false</span><span class="p">),</span> <span class="n">interaction_customization</span><span class="p">(</span><span class="nb">false</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-s&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">simple</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Simple visualisation example</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-c&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">custom_c</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Custom colour visualisation example</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-r&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">rgb</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;RGB colour visualisation example</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-n&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">normals</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Normals visualisation example</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-a&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">shapes</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Shapes visualisation example</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-v&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">viewports</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Viewports example</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">console</span><span class="o">::</span><span class="n">find_argument</span> <span class="p">(</span><span class="n">argc</span><span class="p">,</span> <span class="n">argv</span><span class="p">,</span> <span class="s">&quot;-i&quot;</span><span class="p">)</span> <span class="o">&gt;=</span> <span class="mi">0</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">interaction_customization</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Interaction Customization example</span><span class="se">\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="p">}</span>
  <span class="k">else</span>
  <span class="p">{</span>
    <span class="n">printUsage</span> <span class="p">(</span><span class="n">argv</span><span class="p">[</span><span class="mi">0</span><span class="p">]);</span>
    <span class="k">return</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// ------------------------------------</span>
  <span class="c1">// -----Create example point cloud-----</span>
  <span class="c1">// ------------------------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">basic_cloud_ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">point_cloud_ptr</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Genarating example point clouds.</span><span class="se">\n\n</span><span class="s">&quot;</span><span class="p">;</span>
  <span class="c1">// We&#39;re going to make an ellipse extruded along the z-axis. The colour for</span>
  <span class="c1">// the XYZRGB cloud will gradually go from red to green to blue.</span>
  <span class="kt">uint8_t</span> <span class="nf">r</span><span class="p">(</span><span class="mi">255</span><span class="p">),</span> <span class="n">g</span><span class="p">(</span><span class="mi">15</span><span class="p">),</span> <span class="n">b</span><span class="p">(</span><span class="mi">15</span><span class="p">);</span>
  <span class="k">for</span> <span class="p">(</span><span class="kt">float</span> <span class="n">z</span><span class="p">(</span><span class="o">-</span><span class="mf">1.0</span><span class="p">);</span> <span class="n">z</span> <span class="o">&lt;=</span> <span class="mf">1.0</span><span class="p">;</span> <span class="n">z</span> <span class="o">+=</span> <span class="mf">0.05</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">float</span> <span class="n">angle</span><span class="p">(</span><span class="mf">0.0</span><span class="p">);</span> <span class="n">angle</span> <span class="o">&lt;=</span> <span class="mf">360.0</span><span class="p">;</span> <span class="n">angle</span> <span class="o">+=</span> <span class="mf">5.0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span> <span class="n">basic_point</span><span class="p">;</span>
      <span class="n">basic_point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="mf">0.5</span> <span class="o">*</span> <span class="n">cosf</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span><span class="p">(</span><span class="n">angle</span><span class="p">));</span>
      <span class="n">basic_point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">sinf</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">deg2rad</span><span class="p">(</span><span class="n">angle</span><span class="p">));</span>
      <span class="n">basic_point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">z</span><span class="p">;</span>
      <span class="n">basic_cloud_ptr</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="n">basic_point</span><span class="p">);</span>

      <span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span> <span class="n">point</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">x</span> <span class="o">=</span> <span class="n">basic_point</span><span class="p">.</span><span class="n">x</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">y</span> <span class="o">=</span> <span class="n">basic_point</span><span class="p">.</span><span class="n">y</span><span class="p">;</span>
      <span class="n">point</span><span class="p">.</span><span class="n">z</span> <span class="o">=</span> <span class="n">basic_point</span><span class="p">.</span><span class="n">z</span><span class="p">;</span>
      <span class="kt">uint32_t</span> <span class="n">rgb</span> <span class="o">=</span> <span class="p">(</span><span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="o">&gt;</span><span class="p">(</span><span class="n">r</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="mi">16</span> <span class="o">|</span>
              <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="o">&gt;</span><span class="p">(</span><span class="n">g</span><span class="p">)</span> <span class="o">&lt;&lt;</span> <span class="mi">8</span> <span class="o">|</span> <span class="k">static_cast</span><span class="o">&lt;</span><span class="kt">uint32_t</span><span class="o">&gt;</span><span class="p">(</span><span class="n">b</span><span class="p">));</span>
      <span class="n">point</span><span class="p">.</span><span class="n">rgb</span> <span class="o">=</span> <span class="o">*</span><span class="k">reinterpret_cast</span><span class="o">&lt;</span><span class="kt">float</span><span class="o">*&gt;</span><span class="p">(</span><span class="o">&amp;</span><span class="n">rgb</span><span class="p">);</span>
      <span class="n">point_cloud_ptr</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">point</span><span class="p">);</span>
    <span class="p">}</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">z</span> <span class="o">&lt;</span> <span class="mf">0.0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">r</span> <span class="o">-=</span> <span class="mi">12</span><span class="p">;</span>
      <span class="n">g</span> <span class="o">+=</span> <span class="mi">12</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="k">else</span>
    <span class="p">{</span>
      <span class="n">g</span> <span class="o">-=</span> <span class="mi">12</span><span class="p">;</span>
      <span class="n">b</span> <span class="o">+=</span> <span class="mi">12</span><span class="p">;</span>
    <span class="p">}</span>
  <span class="p">}</span>
  <span class="n">basic_cloud_ptr</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">basic_cloud_ptr</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>
  <span class="n">basic_cloud_ptr</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
  <span class="n">point_cloud_ptr</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">point_cloud_ptr</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>
  <span class="n">point_cloud_ptr</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>

  <span class="c1">// ----------------------------------------------------------------</span>
  <span class="c1">// -----Calculate surface normals with a search radius of 0.05-----</span>
  <span class="c1">// ----------------------------------------------------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">point_cloud_ptr</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_normals1</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">0.05</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_normals1</span><span class="p">);</span>

  <span class="c1">// ---------------------------------------------------------------</span>
  <span class="c1">// -----Calculate surface normals with a search radius of 0.1-----</span>
  <span class="c1">// ---------------------------------------------------------------</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_normals2</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">0.1</span><span class="p">);</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_normals2</span><span class="p">);</span>

  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span><span class="p">;</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">simple</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">simpleVis</span><span class="p">(</span><span class="n">basic_cloud_ptr</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">rgb</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">rgbVis</span><span class="p">(</span><span class="n">point_cloud_ptr</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">custom_c</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">customColourVis</span><span class="p">(</span><span class="n">basic_cloud_ptr</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">normals</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">normalsVis</span><span class="p">(</span><span class="n">point_cloud_ptr</span><span class="p">,</span> <span class="n">cloud_normals2</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">shapes</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">shapesVis</span><span class="p">(</span><span class="n">point_cloud_ptr</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">viewports</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">viewportsVis</span><span class="p">(</span><span class="n">point_cloud_ptr</span><span class="p">,</span> <span class="n">cloud_normals1</span><span class="p">,</span> <span class="n">cloud_normals2</span><span class="p">);</span>
  <span class="p">}</span>
  <span class="k">else</span> <span class="k">if</span> <span class="p">(</span><span class="n">interaction_customization</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">viewer</span> <span class="o">=</span> <span class="n">interactionCustomizationVis</span><span class="p">();</span>
  <span class="p">}</span>

  <span class="c1">//--------------------</span>
  <span class="c1">// -----Main loop-----</span>
  <span class="c1">//--------------------</span>
  <span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
    <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">microseconds</span> <span class="p">(</span><span class="mi">100000</span><span class="p">));</span>
  <span class="p">}</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<div class="section" id="compiling-and-running-the-program">
<h2>Compiling and running the program</h2>
<p>Create a <cite>CMakeLists.txt</cite> file with the following contents:</p>
<div class="highlight-cmake"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
12</pre></div></td><td class="code"><div class="highlight"><pre><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.6</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">pcl_visualizer_viewports</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">pcl_visualizer_demo</span> <span class="s">pcl_visualizer_demo.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">pcl_visualizer_demo</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it like so:</p>
<div class="highlight-python"><div class="highlight"><pre>$ ./pcl_visualizer_demo -h
</pre></div>
</div>
<p>Change the option to change which demo is executed. See the help output
for details.</p>
<p>To exit the viewer application, press <tt class="docutils literal"><span class="pre">q</span></tt>. Press <tt class="docutils literal"><span class="pre">r</span></tt> to centre and
zoom the viewer so that the entire cloud is visible. Use the mouse to
rotate the viewpoint by clicking and dragging. You can use the scroll
wheel, or right-click and drag up and down, to zoom in and out.
Middle-clicking and dragging will move the camera.</p>
</div>
</div>
<div class="section" id="visualising-a-single-cloud">
<h1>Visualising a single cloud</h1>
<p>This sample uses PCLVisualizer to display a single PointXYZ cloud. It
also illustrates changing the background colour and displaying the axes.
The code is in the function <tt class="docutils literal"><span class="pre">simpleVis</span></tt>.</p>
<a class="reference internal image-reference" href="_images/pcl_visualizer_simple.png"><img alt="_images/pcl_visualizer_simple.png" src="_images/pcl_visualizer_simple.png" style="width: 838px;" /></a>
<div class="section" id="explanation">
<h2>Explanation</h2>
<p>The <tt class="docutils literal"><span class="pre">simpleVis</span></tt> function shows how to perform the most basic
visualisation of a point cloud. Let&#8217;s take a look at the function,
line-by-line.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This creates the viewer object, giving it a nice name to display in the
title bar. We are storing it in a boost::shared_ptr only so it can be
passed around the demo program. Usually, you do not need to do this.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>The background colour of the viewer can be set to any RGB colour you
like. In this case, we are setting it to black.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This is the most important line. We add the point cloud to the viewer,
giving it an ID string that can be used to identify the cloud in other
methods. Multiple point clouds can be added with multiple calls to
<tt class="docutils literal"><span class="pre">addPointCloud()</span></tt>, supplying a new ID each time. If you want to update
a point cloud that is already displayed, you must first call
<tt class="docutils literal"><span class="pre">removePointCloud()</span></tt> and provide the ID of the cloud that is to be
updated. (Note: versions 1.1 and up of PCL provide a new API method,
<tt class="docutils literal"><span class="pre">updatePointCloud()</span></tt>, that allows a cloud to be updated without
manually calling <tt class="docutils literal"><span class="pre">removePointCloud()</span></tt> first.)</p>
<p>This is the most basic of <tt class="docutils literal"><span class="pre">addPointCloud()</span></tt>&#8216;s many
variations. Others are used to handle different point types, display
normals, and so on. We will illustrate some others during this tutorial,
or you can see the <a class="reference external" href="http://docs.pointclouds.org/1.0.0/classpcl_1_1visualization_1_1_p_c_l_visualizer.html">PCLVisualizer documentation</a> for more details.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">1</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This next line changes the size of the rendered points. You can control
the way any point cloud is rendered in the viewer using this method.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Viewing complex point clouds can often be disorientating. To keep
yourself aligned in the world, axes can be displayed. These will appear
as three cylinders along the X (red), Y (green) and Z (blue) axes. The
size of the cylinders can be controlled using the <tt class="docutils literal"><span class="pre">scale</span></tt> parameter.
In this case, we have set it to 1.0 (which also happens to be the
default if no value is given). An alternative version of this method can
be used to place the axes at any point in the world.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This final call sets up some handy camera parameters to make things look
nice.</p>
<p>There is one final piece of code relevant to all the samples. It can be
found at the bottom of the sample:</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="k">while</span> <span class="p">(</span><span class="o">!</span><span class="n">viewer</span><span class="o">-&gt;</span><span class="n">wasStopped</span> <span class="p">())</span>
<span class="p">{</span>
  <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">spinOnce</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">this_thread</span><span class="o">::</span><span class="n">sleep</span> <span class="p">(</span><span class="n">boost</span><span class="o">::</span><span class="n">posix_time</span><span class="o">::</span><span class="n">microseconds</span> <span class="p">(</span><span class="mi">100000</span><span class="p">));</span>
<span class="p">}</span>
<span class="p">...</span>
</pre></div>
</div>
<p>These lines are running an event loop. Each call to <tt class="docutils literal"><span class="pre">spinOnce</span></tt> gives
the viewer time to process events, allowing it to be interactive. There
is also a <tt class="docutils literal"><span class="pre">spin</span></tt> method, which only needs to be called once.</p>
</div>
</div>
<div class="section" id="adding-some-colour">
<h1>Adding some colour</h1>
<p>Often, a point cloud will not use the simple PointXYZ type. One common
point type is PointXYZRGB, which also contains colour data. Aside from
that, you may wish to colour specific point clouds to make them
distinguishable in the viewer. PCLVizualizer provides facilities for
displaying point clouds with the colour data stored within them, or for
assigning colours to point clouds.</p>
<div class="section" id="rgb-point-clouds">
<h2>RGB point clouds</h2>
<p>Many devices, such as the Microsoft Kinect, produce point clouds with
RGB data. PCLVisualizer can display the cloud using this data to colour
each point. The code in the <tt class="docutils literal"><span class="pre">rgbVis</span></tt> function shows how to do this.</p>
<a class="reference internal image-reference" href="_images/pcl_visualizer_color_rgb.png"><img alt="_images/pcl_visualizer_color_rgb.png" src="_images/pcl_visualizer_color_rgb.png" style="width: 838px;" /></a>
</div>
<div class="section" id="id1">
<h2>Explanation</h2>
<p>Not much of the code in this sample has changed from the earlier sample.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">rgbVis</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">)</span>
<span class="p">...</span>
</pre></div>
</div>
<p>First, notice that the point type has changed from the simple example.
We now use a point type that also provides room for RGB data. This is
important; without the RGB fields in the point (the point type does not
necessarily have to be <tt class="docutils literal"><span class="pre">PointXYZRGB</span></tt>, as long as it has the three
colour fields), PCLVisualizer would not know what colours to use.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGB</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">rgb</span><span class="p">(</span><span class="n">point_cloud_ptr</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Next, after setting the viewer&#8217;s background colour, we create a colour
handler object. PCLVisualizer uses objects like this to display custom
data. In this case, the object will get the RGB colour fields from each
point for the viewer to use when drawing them. Many other handlers exist
for a wide range of purposes. We will see another of the colour handlers
in the next code sample, but handlers also exist for such purposes as
drawing any other field as the colour and drawing geometry from point
clouds. See the <a class="reference external" href="http://docs.pointclouds.org/1.0.0/group__visualization.html">documentation</a> for details.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Finally, when we add the point cloud, we specify the colour handler when
we add the point cloud to the viewer.</p>
</div>
<div class="section" id="custom-colours">
<h2>Custom colours</h2>
<p>The second code sample demonstrates giving a point cloud a single
colour. We can use this technique to give specific point clouds their
own colours, allowing us to distinguish individual point clouds. In this
sample, given in the <tt class="docutils literal"><span class="pre">customColourVis</span></tt> function, we have set the point
cloud&#8217;s colour to green. (We have also increased the size of the points
to make the colour more visible.)</p>
<a class="reference internal image-reference" href="_images/pcl_visualizer_color_custom.png"><img alt="_images/pcl_visualizer_color_custom.png" src="_images/pcl_visualizer_color_custom.png" style="width: 838px;" /></a>
</div>
<div class="section" id="id2">
<h2>Explanation</h2>
<p>Again, not much of the code in this sample has changed from the earlier
sample.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">customColourVis</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">ConstPtr</span> <span class="n">cloud</span><span class="p">)</span>
<span class="p">...</span>
</pre></div>
</div>
<p>The point type in use this time is back to PointXYZ again. When setting
a custom colour handler for a point cloud, it doesn&#8217;t matter what the
underlying data type is. None of the point fields are used for the
colour with the custom colour handler.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">single_color</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>We create a custom colour handler and assign it a nice, bright shade of
green.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">single_color</span><span class="p">,</span> <span class="s">&quot;sample cloud&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>As with the previous example, we pass the colour handler in when we call
<tt class="docutils literal"><span class="pre">addPointCloud&lt;&gt;()</span></tt>.</p>
</div>
</div>
<div class="section" id="normals-and-other-information">
<h1>Normals and other information</h1>
<p>Displaying normals is an important step in understanding a point cloud.
The PCLVisualizer class has the ability to draw normals, as well as
other interesting point cloud information, such as principal curvatures
and geometries.</p>
<p>The code sample in the <tt class="docutils literal"><span class="pre">normalsVis</span></tt> function shows how to display the
normals of a point cloud. The code for calculating the normals will not
be explained in this tutorial. See the normals calculation tutorial for
details.</p>
<a class="reference internal image-reference" href="_images/pcl_visualizer_normals.png"><img alt="_images/pcl_visualizer_normals.png" src="_images/pcl_visualizer_normals.png" style="width: 838px;" /></a>
<div class="section" id="id3">
<h2>Explanation</h2>
<p>The relevant line of code is placed after the line to draw the point
cloud.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloudNormals</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">normals</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="s">&quot;normals&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Once you have your normals, one extra line is all it takes to display
them in the viewer. The parameters to this method set the number of
normals to display (here, every tenth normal is displayed) and the
length of the line to draw for each normal (0.05, in this case).</p>
</div>
</div>
<div class="section" id="drawing-shapes">
<h1>Drawing Shapes</h1>
<p>PCLVisualizer allows you to draw various primitive shapes in the view.
This is often used to visualise the results of point cloud processing
algorithms, for example, visualising which clusters of points have been
recognised as landmarks by drawing transparent spheres around them.</p>
<p>The sample code in the <tt class="docutils literal"><span class="pre">shapesVis</span></tt> function illustrates some of the
methods used to add shapes to a viewer. It adds four shapes:</p>
<ul class="simple">
<li>A line from the first point in the cloud to the last point in the
cloud.</li>
<li>A plane at the origin.</li>
<li>A sphere centred on the first point in the cloud.</li>
<li>A cone along the Y-axis.</li>
</ul>
<a class="reference internal image-reference" href="_images/pcl_visualizer_shapes.png"><img alt="_images/pcl_visualizer_shapes.png" src="_images/pcl_visualizer_shapes.png" style="width: 838px;" /></a>
<div class="section" id="id4">
<h2>Explanation</h2>
<p>The relevant parts of the code sample for drawing shapes begin after the
point cloud is added to the viewer.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addLine</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">size</span><span class="p">()</span> <span class="o">-</span> <span class="mi">1</span><span class="p">],</span> <span class="s">&quot;line&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This line (of code) adds a line (in space) from the first point in the
cloud to the last point. This method is useful, for example, for showing
correspondences between point clouds. In this case, the line is using
the default colour, but you can also specify the colour of the line.
Drawing shapes at points from a point cloud is very common, and various
shapes are available.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addSphere</span> <span class="p">(</span><span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="mi">0</span><span class="p">],</span> <span class="mf">0.2</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="s">&quot;sphere&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This next line adds a sphere centred on the first point in the cloud
with a radius of 0.2. It also gives the sphere a colour.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span> <span class="n">coeffs</span><span class="p">;</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPlane</span> <span class="p">(</span><span class="n">coeffs</span><span class="p">,</span> <span class="s">&quot;plane&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Next, we add a plane to the drawing. In this case, we are specifying the
plane using the standard plane equation (ax + by + cz + d = 0). Our
plane will be centered at the origin and oriented along the Z-axis. Many
of the shape drawing functions take coefficients in this way.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">clear</span><span class="p">();</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">0.3</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">0.3</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">0.0</span><span class="p">);</span>
<span class="n">coeffs</span><span class="p">.</span><span class="n">values</span><span class="p">.</span><span class="n">push_back</span><span class="p">(</span><span class="mf">5.0</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCone</span> <span class="p">(</span><span class="n">coeffs</span><span class="p">,</span> <span class="s">&quot;cone&quot;</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Finally, we add a cone. We are again using model coefficients to specify
the cone&#8217;s parameters.</p>
</div>
</div>
<div class="section" id="multiple-viewports">
<h1>Multiple viewports</h1>
<p>You will often want to compare multiple point clouds side-by-side. While
you could draw them in the same view port, this can get confusing.
PCLVisualizer allows you to draw multiple point clouds in separate
viewports, making comparison easy.</p>
<p>The code in the <tt class="docutils literal"><span class="pre">viewportsVis</span></tt> function uses viewports to demonstrate
comparing the normals calculated for a point cloud. Two sets of normals
are calculated for the same cloud but using a different search radius.
The first time, the search radius is 0.05. The second time, it is 0.1.
The normals for the 0.05 radius search are displayed in the viewport
with the black background. The normals for the 0.1 radius search are
displayed in the viewport with the grey background.</p>
<p>Comparing the two sets of normals side-by-side makes it immediately
obvious what the effects of the different algorithm parameter are. In
this way, you can experiment with the parameters for algorithms to find
good settings, quickly viewing the results.</p>
<a class="reference internal image-reference" href="_images/pcl_visualizer_viewports.png"><img alt="_images/pcl_visualizer_viewports.png" src="_images/pcl_visualizer_viewports.png" style="width: 838px;" /></a>
<div class="section" id="id5">
<h2>Explanation</h2>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This is our standard code for creating a viewer.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="kt">int</span> <span class="n">v1</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">0.5</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addText</span> <span class="p">(</span><span class="s">&quot;Radius: 0.01&quot;</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;v1 text&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerRGBField</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">rgb</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">rgb</span><span class="p">,</span> <span class="s">&quot;sample cloud1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>The next step is to create a new viewport. The four parameters are the
minimum and maximum ranges of the viewport on the X- and Y-axes, between
0 and 1. We are creating a viewport that will fill the left half of the
window. We must store the view port ID number that is passed back in the
fifth parameter and use it in all other calls where we only want to
affect that viewport.</p>
<p>We also set the background colour of this viewport, give it a lable
based on what we are using the viewport to distinguish, and add our
point cloud to it, using an RGB colour handler.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="kt">int</span> <span class="n">v2</span><span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">createViewPort</span> <span class="p">(</span><span class="mf">0.5</span><span class="p">,</span> <span class="mf">0.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="mf">1.0</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setBackgroundColor</span> <span class="p">(</span><span class="mf">0.3</span><span class="p">,</span> <span class="mf">0.3</span><span class="p">,</span> <span class="mf">0.3</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addText</span> <span class="p">(</span><span class="s">&quot;Radius: 0.1&quot;</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="s">&quot;v2 text&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
<span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PointCloudColorHandlerCustom</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="n">single_color</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="mi">0</span><span class="p">,</span> <span class="mi">255</span><span class="p">,</span> <span class="mi">0</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">single_color</span><span class="p">,</span> <span class="s">&quot;sample cloud2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Then we do the same thing again for the second viewport, making it take
up the right half of the window. We make this viewport a shade of grey
so it is easily distinguishable in the demonstration program. We add the
same point cloud, but this time we give it a custom colour handler.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud1&quot;</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">setPointCloudRenderingProperties</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCL_VISUALIZER_POINT_SIZE</span><span class="p">,</span> <span class="mi">3</span><span class="p">,</span> <span class="s">&quot;sample cloud2&quot;</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addCoordinateSystem</span> <span class="p">(</span><span class="mf">1.0</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>These three lines set some properties globally for all viewports. Most of
the PCLVisualizer methods accept an optional viewport ID parameter. When
it is specified, they affect only that viewport. When it is not, as in
this case, they affect all viewports.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloudNormals</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">normals1</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="s">&quot;normals1&quot;</span><span class="p">,</span> <span class="n">v1</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addPointCloudNormals</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZRGB</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">normals2</span><span class="p">,</span> <span class="mi">10</span><span class="p">,</span> <span class="mf">0.05</span><span class="p">,</span> <span class="s">&quot;normals2&quot;</span><span class="p">,</span> <span class="n">v2</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>Finally, we add the normals, one to each viewport.</p>
</div>
</div>
<div class="section" id="interaction-customization">
<h1>Interaction Customization</h1>
<p>You will sometimes feel that the interactivity options offered by the default
mouse and key bindings do not satisfy your needs and you may want to extend
functionality with features such as the possibility of saving the currently
shown point clouds when pressing a button or annotating certain locations on the
rendering window with your mouse etc. A very simple example of such things
is found in the <tt class="docutils literal"><span class="pre">interactionCustomizationVis</span></tt> method.</p>
<p>In this part of the tutorial you will be shown how to catch mouse and keyboard
events. By right clicking on the window, a 2D text will appear and you can
erase all the text instances by pressing &#8216;r&#8217;. The result should look something
like this:</p>
<a class="reference internal image-reference" href="_images/pcl_visualizer_interaction_customization.png"><img alt="_images/pcl_visualizer_interaction_customization.png" src="_images/pcl_visualizer_interaction_customization.png" style="width: 838px;" /></a>
<div class="section" id="id6">
<h2>Explanation</h2>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span> <span class="p">(</span><span class="s">&quot;3D Viewer&quot;</span><span class="p">));</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">initCameraParameters</span> <span class="p">();</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This is the standard code for instantiating a viewer.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">registerKeyboardCallback</span> <span class="p">(</span><span class="n">keyboardEventOccurred</span><span class="p">,</span> <span class="p">(</span><span class="kt">void</span><span class="o">*</span><span class="p">)</span><span class="o">&amp;</span><span class="n">viewer</span><span class="p">);</span>
<span class="n">viewer</span><span class="o">-&gt;</span><span class="n">registerMouseCallback</span> <span class="p">(</span><span class="n">mouseEventOccurred</span><span class="p">,</span> <span class="p">(</span><span class="kt">void</span><span class="o">*</span><span class="p">)</span><span class="o">&amp;</span><span class="n">viewer</span><span class="p">);</span>
<span class="p">...</span>
</pre></div>
</div>
<p>These two lines of code will register the two methods, <tt class="docutils literal"><span class="pre">keyboardEventOccurred</span></tt>
and <tt class="docutils literal"><span class="pre">mouseEventOccurred</span></tt> to the keyboard and mouse event callback, respectively.
The second arguments for the two method calls are the so-called cookies. These
are any parameters you might want to pass to the callback function. In our case,
we want to pass the viewer itself, in order to do modifications on it in case
of user interaction. Note that these arguments must be in the form of a single
<tt class="docutils literal"><span class="pre">void*</span></tt> instance, so we need to cast the pointer to our <tt class="docutils literal"><span class="pre">boost::shared_ptr</span></tt> to <tt class="docutils literal"><span class="pre">void*</span></tt>.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="kt">void</span> <span class="n">mouseEventOccurred</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">MouseEvent</span> <span class="o">&amp;</span><span class="n">event</span><span class="p">,</span>
                     <span class="kt">void</span><span class="o">*</span> <span class="n">viewer_void</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="o">=</span> <span class="o">*</span><span class="k">static_cast</span><span class="o">&lt;</span><span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="o">*&gt;</span> <span class="p">(</span><span class="n">viewer_void</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">event</span><span class="p">.</span><span class="n">getButton</span> <span class="p">()</span> <span class="o">==</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">MouseEvent</span><span class="o">::</span><span class="n">LeftButton</span> <span class="o">&amp;&amp;</span> <span class="n">event</span><span class="p">.</span><span class="n">getType</span> <span class="p">()</span> <span class="o">==</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">MouseEvent</span><span class="o">::</span><span class="n">MouseButtonRelease</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Left mouse button released at position (&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">event</span><span class="p">.</span><span class="n">getX</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;, &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">event</span><span class="p">.</span><span class="n">getY</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;)&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="kt">char</span> <span class="n">str</span><span class="p">[</span><span class="mi">512</span><span class="p">];</span>

    <span class="n">sprintf</span> <span class="p">(</span><span class="n">str</span><span class="p">,</span> <span class="s">&quot;text#%03d&quot;</span><span class="p">,</span> <span class="n">text_id</span> <span class="o">++</span><span class="p">);</span>
    <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">addText</span> <span class="p">(</span><span class="s">&quot;clicked here&quot;</span><span class="p">,</span> <span class="n">event</span><span class="p">.</span><span class="n">getX</span> <span class="p">(),</span> <span class="n">event</span><span class="p">.</span><span class="n">getY</span> <span class="p">(),</span> <span class="n">str</span><span class="p">);</span>
  <span class="p">}</span>
<span class="p">}</span>
<span class="p">...</span>
</pre></div>
</div>
<p>This is the method that handles the mouse events. Every time any kind of mouse
event is registered, this function will be called. In order to see exactly what
that event is, we need to extract that information from the <tt class="docutils literal"><span class="pre">event</span></tt> instance.
In our case, we are looking for left mouse button releases. Whenever such an event
happens, we shall write a small text at the position of the mouse click.</p>
<div class="highlight-cpp"><div class="highlight"><pre><span class="p">...</span>
<span class="kt">void</span> <span class="n">keyboardEventOccurred</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">KeyboardEvent</span> <span class="o">&amp;</span><span class="n">event</span><span class="p">,</span>
                        <span class="kt">void</span><span class="o">*</span> <span class="n">viewer_void</span><span class="p">)</span>
<span class="p">{</span>
  <span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="n">viewer</span> <span class="o">=</span> <span class="o">*</span><span class="k">static_cast</span><span class="o">&lt;</span><span class="n">boost</span><span class="o">::</span><span class="n">shared_ptr</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">visualization</span><span class="o">::</span><span class="n">PCLVisualizer</span><span class="o">&gt;</span> <span class="o">*&gt;</span> <span class="p">(</span><span class="n">viewer_void</span><span class="p">);</span>
  <span class="k">if</span> <span class="p">(</span><span class="n">event</span><span class="p">.</span><span class="n">getKeySym</span> <span class="p">()</span> <span class="o">==</span> <span class="s">&quot;r&quot;</span> <span class="o">&amp;&amp;</span> <span class="n">event</span><span class="p">.</span><span class="n">keyDown</span> <span class="p">())</span>
  <span class="p">{</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;r was pressed =&gt; removing all text&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

    <span class="kt">char</span> <span class="n">str</span><span class="p">[</span><span class="mi">512</span><span class="p">];</span>
    <span class="k">for</span> <span class="p">(</span><span class="kt">unsigned</span> <span class="kt">int</span> <span class="n">i</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span> <span class="n">i</span> <span class="o">&lt;</span> <span class="n">text_id</span><span class="p">;</span> <span class="o">++</span><span class="n">i</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">sprintf</span> <span class="p">(</span><span class="n">str</span><span class="p">,</span> <span class="s">&quot;text#%03d&quot;</span><span class="p">,</span> <span class="n">i</span><span class="p">);</span>
      <span class="n">viewer</span><span class="o">-&gt;</span><span class="n">removeShape</span> <span class="p">(</span><span class="n">str</span><span class="p">);</span>
    <span class="p">}</span>
    <span class="n">text_id</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="p">}</span>
<span class="p">}</span>
<span class="p">...</span>
</pre></div>
</div>
<p>The same approach applies for the keyboard events. We check what key was pressed
and the action we do is to remove all the text created by our mouse clicks.
Please note that when &#8216;r&#8217; is pressed, the 3D camera still resets, as per
the original binding of &#8216;r&#8217; inside PCLVisualizer. So, our keyboard events do not
overwrite the functionality of the base class.</p>
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