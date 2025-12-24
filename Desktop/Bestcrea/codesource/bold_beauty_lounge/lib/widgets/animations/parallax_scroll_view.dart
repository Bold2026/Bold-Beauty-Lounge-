import 'package:flutter/material.dart';

class ParallaxScrollView extends StatefulWidget {
  final List<Widget> children;
  final double parallaxFactor;
  final ScrollController? controller;

  const ParallaxScrollView({
    super.key,
    required this.children,
    this.parallaxFactor = 0.5,
    this.controller,
  });

  @override
  State<ParallaxScrollView> createState() => _ParallaxScrollViewState();
}

class _ParallaxScrollViewState extends State<ParallaxScrollView> {
  late ScrollController _scrollController;

  @override
  void initState() {
    super.initState();
    _scrollController = widget.controller ?? ScrollController();
  }

  @override
  void dispose() {
    if (widget.controller == null) {
      _scrollController.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return NotificationListener<ScrollNotification>(
      onNotification: (notification) {
        if (notification is ScrollUpdateNotification) {
          setState(() {});
        }
        return false;
      },
      child: ListView.builder(
        controller: _scrollController,
        itemCount: widget.children.length,
        itemBuilder: (context, index) {
          return ParallaxWidget(
            scrollController: _scrollController,
            parallaxFactor: widget.parallaxFactor,
            child: widget.children[index],
          );
        },
      ),
    );
  }
}

class ParallaxWidget extends StatelessWidget {
  final Widget child;
  final ScrollController scrollController;
  final double parallaxFactor;

  const ParallaxWidget({
    super.key,
    required this.child,
    required this.scrollController,
    this.parallaxFactor = 0.5,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: scrollController,
      builder: (context, _) {
        final scrollOffset = scrollController.hasClients
            ? scrollController.offset
            : 0.0;

        return Transform.translate(
          offset: Offset(0, scrollOffset * parallaxFactor),
          child: child,
        );
      },
    );
  }
}

class ParallaxImage extends StatelessWidget {
  final String imagePath;
  final double height;
  final BoxFit fit;
  final double parallaxFactor;
  final ScrollController scrollController;

  const ParallaxImage({
    super.key,
    required this.imagePath,
    required this.height,
    required this.scrollController,
    this.fit = BoxFit.cover,
    this.parallaxFactor = 0.5,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: scrollController,
      builder: (context, _) {
        final scrollOffset = scrollController.hasClients
            ? scrollController.offset
            : 0.0;

        return Transform.translate(
          offset: Offset(0, scrollOffset * parallaxFactor),
          child: Container(
            height: height,
            decoration: BoxDecoration(
              image: DecorationImage(image: AssetImage(imagePath), fit: fit),
            ),
          ),
        );
      },
    );
  }
}

class ParallaxCard extends StatelessWidget {
  final Widget child;
  final EdgeInsets? padding;
  final EdgeInsets? margin;
  final Color? backgroundColor;
  final double? elevation;
  final BorderRadius? borderRadius;
  final double parallaxFactor;
  final ScrollController scrollController;

  const ParallaxCard({
    super.key,
    required this.child,
    required this.scrollController,
    this.padding,
    this.margin,
    this.backgroundColor,
    this.elevation,
    this.borderRadius,
    this.parallaxFactor = 0.3,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: scrollController,
      builder: (context, _) {
        final scrollOffset = scrollController.hasClients
            ? scrollController.offset
            : 0.0;

        return Transform.translate(
          offset: Offset(0, scrollOffset * parallaxFactor),
          child: Card(
            margin: margin,
            elevation: elevation,
            color: backgroundColor,
            shape: borderRadius != null
                ? RoundedRectangleBorder(borderRadius: borderRadius!)
                : null,
            child: Padding(
              padding: padding ?? const EdgeInsets.all(16),
              child: child,
            ),
          ),
        );
      },
    );
  }
}





